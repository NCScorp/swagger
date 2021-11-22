import angular = require('angular');

import { IContrato } from '../interfaces/contrato.interface';
import { ContratosService, IGetAllFilters } from '../contratos.service';
import { NsjTreeGridClasses } from '@shared/components/nsjtreegrid/components/nsjtreegrid.classes';
import { IClienteTree } from './interfaces/iclientetree.interface';
import { ESituacaoFinanceiraContrato } from '../interfaces/esituacaocontrato.enum';
import { EContratoStatus } from './interfaces/econtratostatus.enum';
import { IContratoTree } from './interfaces/icontratotree.interface';
import { IContratoItemTree } from './interfaces/icontratoitemtree.interface';
import { formatarCnpj, formatarCpf, formatarDinheiro } from '@utils/utils';
import { IContratoApi, IContratoListaContratoApi } from '../interfaces/icontratoapi.interface';
import { IRequisicaoApi } from './interfaces/ireqapi.interface';
import moment from 'moment';


export class ContratosIndexController {
    static $inject = [
        '$scope',
        '$stateParams',
        'contratosService',
    ];

    /**
     * Filtros da listagem
     */
    filtros = {
        texto: '',
        situacaoContrato: null,
        periodo: {
            inicio: null,
            fim: null
        }
    }
    /**
     * Cards de contrato apresentados na tela
     */
    cardsContrato: {
        codigo: string;
        titulo: string;
        qtd: number;
        perc: number;
    }[] = [];
    /**
     * Define se a tela está processando alguma informação
     */
    busy: boolean = false;
    /**
     * Mensagem de carregamento
     */
    busyMensagem = 'Carregando...';
    /**
     * Configuração da tree de contratos
     */
    treeContratosConfig: NsjTreeGridClasses.Config;
    /**
     * Listagem de dados utilizados para popular a tree
     */
    arrContratosPorClientes: IClienteTree[] =[];
    /**
     * Enumerado de situação financeira do contrato
     */
    ESituacaoFinanceiraContrato = ESituacaoFinanceiraContrato;
    /**
     * Container da tabela
     */
    tabContainer: Element;
    /**
     * Armazena dados da última requisição da api feita
     */
    requisicaoApi: IRequisicaoApi = null;

    constructor(
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public contratosService: ContratosService
    ) {
        this.filtros.texto = $stateParams.q ? $stateParams.q : '';
    }

    $onInit(){
        this.configCardsContrato();
        this.carregarDados();
        this.tabContainer = document.querySelector('.tabela-container');
        this.tabContainer.addEventListener('scroll', (ev) => {
            this.onTabelaScrollChange();
        });
    }
    
    onTabelaScrollChange(){
        let scrollMax = (<any>this.tabContainer).scrollTopMax;
        const scrollTop = this.tabContainer.scrollTop;
        if (scrollMax > 0 && scrollMax == scrollTop) {
            this.carregarMais();
        }
    }

    /**
     * Carrega os itens da consulta paginada
     */
    async carregarMais(){
        if (this.requisicaoApi == null) {
            return;
        }

        if (this.requisicaoApi.retornoApi.next == null || this.requisicaoApi.retornoApi.next == '') {
            return;
        }

        this.setBusy('Buscando mais...');
        this.reloadScope();
        
        try {
            // Busco lista de contratos
            const dadosApi = await this.contratosService.getAllNext(this.requisicaoApi.retornoApi.next);
            this.requisicaoApi.retornoApi = dadosApi;

            // Converto dados para o formato da tela
            this.converterDadosApiToDadosTree(dadosApi.items, true);

            if (this.arrContratosPorClientes.length > 0) {
                this.treeContratosConfig = this.getConfiguracaoTreeContratos(this.arrContratosPorClientes);
            }
        } catch (error) {
            // Apresento toaster de erro
            return;
        } finally {
            this.busy = false;
            this.reloadScope();
        }
    }

    /**
     * Coloca a tela em modo de processamento
     * @param mensagem 
     */
    setBusy(mensagem = 'Carregando...'){
        this.busyMensagem = mensagem;
        this.busy = true;
    }

    /**
     * Atualiza escopo da tela
    */
    reloadScope(){
        this.$scope.$applyAsync();
    }

    /**
     * Formata numero com zero a esquerda
     * @param numero 
     */
    zeroAEsquerda(numero){
        if (numero < 10) {
            return `0${numero}`; 
        } else {
            return `${numero}`;
        }
    }
    
    /**
     * Evento chamado quando um filtro for alterado ou o botão de atualizar for chamado
     */
    onFiltroToolbarChange(){
        this.carregarDados();
    }

    /**
     * Configura os cards de contrato
     */
    configCardsContrato(){
        this.cardsContrato = [];

        this.cardsContrato.push({
            codigo: 'total-contratos',
            titulo: 'TOTAL DE CONTRATOS',
            qtd: 0,
            perc: 0
        });
        this.cardsContrato.push({
            codigo: 'contratos-ativos',
            titulo: 'CONTRATOS ATIVOS',
            qtd: 0,
            perc: 0
        });
        this.cardsContrato.push({
            codigo: 'aguardando-aprovacao',
            titulo: 'AGUARDANDO APROVAÇÃO',
            qtd: 0,
            perc: 0
        });
    }

    /**
     * Atualiza porcentagem do valores do card de contrato
     * @param codigo 
     * @param total 
     */
    atualizarCardContrato(codigo, total, qtd){
        const card = this.cardsContrato.find(card => card.codigo == codigo);

        if (card) {
            card.qtd = qtd;
            card.perc = 0;
            if (total > 0) {
                card.perc = Math.round((qtd / total) * 100);
            }
        }
    }

    /**
     * Monta configuração da tree de contratos
     * @param arrDados 
     */
    getConfiguracaoTreeContratos(arrClientesTree: IClienteTree[] = []){
        let nsjTreeGridConfig: NsjTreeGridClasses.Config;

        if (!this.treeContratosConfig) {
            nsjTreeGridConfig = new NsjTreeGridClasses.Config();
            nsjTreeGridConfig.iconExpand = 'fas fa-caret-right';
            nsjTreeGridConfig.iconCollapse = 'fas fa-caret-down';
            nsjTreeGridConfig.expandLevel = 3;
    
            // Configuro colunas
            //Coluna Contratos de Clientes, com icones de acordo com o nível da linha
            const colCodigo = new NsjTreeGridClasses.ColConfig('linha_codigo', 'Contratos de Clientes');
            colCodigo.arrIcones.push(new NsjTreeGridClasses.ColIcon('icon-cliente', 'fas fa-building', 'left', (linha: NsjTreeGridClasses.Row) => {
                return linha.level == 1; // Cliente
            }));
            colCodigo.arrIcones.push(new NsjTreeGridClasses.ColIcon('icon-contrato', 'fas fa-list-alt', 'left', (linha: NsjTreeGridClasses.Row) => {
                return linha.level == 2; // Contrato
            }));
            colCodigo.arrIcones.push(new NsjTreeGridClasses.ColIcon('icon-contrato-item', 'fas fa-circle', 'left', (linha: NsjTreeGridClasses.Row) => {
                return linha.level == 3; // Item de contrato
            }));
            nsjTreeGridConfig.arrColsConfig.push(colCodigo);
    
            //Coluna Descrições
            const colDescricao = new NsjTreeGridClasses.ColConfig('linha_descricao', 'Descrições');
            nsjTreeGridConfig.arrColsConfig.push(colDescricao);
    
            //Coluna Data de registro
            const colDtRegistro = new NsjTreeGridClasses.ColConfig('linha_dt_registro', 'Datas de registro');
            colDtRegistro.isVisible = (linha: NsjTreeGridClasses.Row) => { 
                return linha.level > 1 // Invisível na linha de cliente
            };
            colDtRegistro.alinhamentoH = 'center';
            nsjTreeGridConfig.arrColsConfig.push(colDtRegistro);
    
            //Coluna Valores
            const colValores = new NsjTreeGridClasses.ColConfig('linha_valor', 'Valores');
            colValores.isVisible = (linha: NsjTreeGridClasses.Row) => { 
                return linha.level == 3; // Item de contrato
            };
            nsjTreeGridConfig.arrColsConfig.push(colValores);
    
            //Coluna Situação Financeira do Contrato
            /* @TODO: Definição da situação financeira do contrato
            const colSitucaoFinanceiraContrato = new NsjTreeGridClasses.ColConfig('linha_sit_fin', 'Situação financeira do contrato');
            colSitucaoFinanceiraContrato.isVisible = (linha: NsjTreeGridClasses.Row) => { 
                return linha.level == 2; // Contrato
            };
            colSitucaoFinanceiraContrato.tipo = NsjTreeGridClasses.EnumColTipo.ectLabel;
            colSitucaoFinanceiraContrato.colConfigLabel = new NsjTreeGridClasses.ColConfigLabel((linha: NsjTreeGridClasses.Row, col: NsjTreeGridClasses.ColConfig) => {
                const situacao: ESituacaoFinanceiraContrato = linha.get('situacao_financeira');
                switch (situacao) {
                    case ESituacaoFinanceiraContrato.scEmDia: return NsjTreeGridClasses.EColConfigLabelClass.cclcInfo;
                    case ESituacaoFinanceiraContrato.scQuitado: return NsjTreeGridClasses.EColConfigLabelClass.cclcGreen;
                    case ESituacaoFinanceiraContrato.scVencido: return NsjTreeGridClasses.EColConfigLabelClass.cclcRed;
                    default: return NsjTreeGridClasses.EColConfigLabelClass.cclcInfo;
                }
            });
            nsjTreeGridConfig.arrColsConfig.push(colSitucaoFinanceiraContrato);
            */
        } else {
            nsjTreeGridConfig = this.treeContratosConfig;
        }
        
        // Limpo linhas
        nsjTreeGridConfig.arrRows = [];

        // Construo Linhas
        arrClientesTree.forEach((cliente) => {
            // Linha de cliente
            const idCliente = 'cliente_' + cliente.cliente;

            nsjTreeGridConfig.arrRows.push(new NsjTreeGridClasses.Row(
                idCliente,
                null, 
                cliente, 
                nsjTreeGridConfig.arrColsConfig
            ));

            // Linha de contratos
            cliente.contratos.forEach((contrato) => {
                const idContrato = 'contrato_' + contrato.contrato;

                nsjTreeGridConfig.arrRows.push(new NsjTreeGridClasses.Row(
                    idContrato,
                    idCliente, 
                    contrato, 
                    nsjTreeGridConfig.arrColsConfig
                ));

                // Linha de itens de contrato
                contrato.itens.forEach((contratoitem) => {
                    const idContratoitem = 'contratoitem_' + contratoitem.contratoitem;

                    nsjTreeGridConfig.arrRows.push(new NsjTreeGridClasses.Row(
                        idContratoitem, 
                        idContrato, 
                        contratoitem, 
                        nsjTreeGridConfig.arrColsConfig
                    ));
                })
            })
        });

        // Retorno configuração da tree grid
        return nsjTreeGridConfig;
    }

    /**
     * Busca dados da api e realiza conversão para dados da tela
     * @returns 
     */
    async carregarDados(){
        this.setBusy();

        // Monto filtros
        const filters: IGetAllFilters = {
            texto: this.filtros.texto,
        };

        try {
            // Busco lista de contratos
            const [dadosApi, totaisContratos] = await Promise.all([
                await this.contratosService.getAll(filters),
                await this.contratosService.getTotaisContratos()
            ]);

            this.requisicaoApi = {
                retornoApi: dadosApi,
                filtros: filters
            }
            
            // Atualizo valores dos cards
            this.atualizarCardContrato(EContratoStatus.csTotal, totaisContratos.total, totaisContratos.total);
            this.atualizarCardContrato(EContratoStatus.csAtivos, totaisContratos.total, totaisContratos.ativos);
            this.atualizarCardContrato(EContratoStatus.csAguardandoAprovacao, totaisContratos.total, totaisContratos.aguardando_ativacao);

            // Volto scroll da tabela ao inicio após recarregar
            this.tabContainer.scrollTo({
                top: 0
            });

            // Converto dados para o formato da tela
            this.converterDadosApiToDadosTree(dadosApi.items);

            if (this.arrContratosPorClientes.length > 0) {
                this.treeContratosConfig = this.getConfiguracaoTreeContratos(this.arrContratosPorClientes);
            }
        } catch (error) {
            // Apresento toaster de erro
            return;
        } finally {
            this.busy = false;
            this.reloadScope();
        }
    }

    /**
     * Converte dados da api para o formato a ser utilizado pela Tree
     * @param arrDadosApi 
     * @param maisItens: Define se está carregando mais itens de acordo com a paginação
     */
    converterDadosApiToDadosTree(arrDadosApi: IContratoListaContratoApi[] = [], maisItens = false){
        const arrParticipanteId = [];
        const arrContratosPorClientes: IClienteTree[] = [];

        // Percorro contratos
        arrDadosApi.forEach((contrato) => {
            const participante = contrato.participante;

            // Verifico se participante já foi convertido para o modelo utilizado pela Tela
            let participanteIndex = arrParticipanteId.indexOf(participante.id_compartilhado);
            let participanteTree: IClienteTree = null;
            if (participanteIndex > -1) {
                participanteTree = arrContratosPorClientes[participanteIndex];
            } else {
                const participanteNome = (participante.nome_fantasia != null && participante.nome_fantasia.length > 0) ?
                    participante.nome_fantasia : participante.razao_social;
                // Converto participante para dados utilizados pela tela
                participanteTree = {
                    cliente: participante.id_compartilhado,
                    linha_codigo: participanteNome,
                    linha_descricao: '',
                    contratos: []
                }
                // CNPJ
                if (participante.cpf_cnpj.length == 14) {
                    participanteTree.linha_descricao = 'CNPJ: ' + formatarCnpj(participante.cpf_cnpj);
                } 
                // CPF
                else if (participante.cpf_cnpj.length == 11) {
                    participanteTree.linha_descricao = 'CPF: ' + formatarCpf(participante.cpf_cnpj);
                }

                arrContratosPorClientes.push(participanteTree);
                arrParticipanteId.push(participante.id_compartilhado);
            }

            // Converto contrato
            const contratoTree: IContratoTree = {
                contrato: contrato.id_compartilhado,
                linha_codigo: contrato.codigo,
                linha_descricao: contrato.descricao,
                linha_dt_registro: moment(contrato.data_registro).format('DD/MM/YYYY'),
                // @TODO: Não se sabe o valor desses campos!!!
                situacao_financeira: ESituacaoFinanceiraContrato.scEmDia,
                linha_sit_fin: 'EM DIA',
                itens: []
            }
            // switch (contrato.situacao_financeira) {
            //     case ESituacaoFinanceiraContrato.scEmDia: {
            //         contratoTree.linha_sit_fin = 'EM DIA';
            //         break;
            //     }
            //     case ESituacaoFinanceiraContrato.scQuitado: {
            //         contratoTree.linha_sit_fin = 'QUITADO';
            //         break;
            //     }
            //     case ESituacaoFinanceiraContrato.scVencido: {
            //         contratoTree.linha_sit_fin = 'VENCIDO';
            //         break;
            //     }
            // }

            // Converto itens
            contrato.itens.forEach((item) => {
                const itemTree: IContratoItemTree = {
                    contratoitem: item.id,
                    linha_codigo: item.codigo_item_contrato,
                    linha_descricao: item.descricao,
                    linha_valor: formatarDinheiro(item.valor_unitario * item.quantidade)
                }

                contratoTree.itens.push(itemTree);
            });

            // Adiciono contrato ao objeto do participante
            participanteTree.contratos.push(contratoTree);
        });

        // Se for uma nova requisição, substituo pelos novos dados
        if (!maisItens) {
            this.arrContratosPorClientes = arrContratosPorClientes;
        } 
        // Senão, faço um merge com os novos contratos
        else {
            arrContratosPorClientes.forEach((participante) => {
                let clienteTree = this.arrContratosPorClientes.find((clienteTree) => {
                    return clienteTree.cliente == participante.cliente;
                });

                // Se participante ainda não está na lista, só adiciono
                if (!clienteTree) {
                    this.arrContratosPorClientes.push(participante);
                }
                // Senão, adiciono ou substituo contratos
                else {
                    participante.contratos.forEach((contrato) => {
                        let contratoTree = clienteTree.contratos.find((contratoTree) => {
                            return contratoTree.contrato == contrato.contrato;
                        });
        
                        // Se contrato ainda não está na lista, só adiciono
                        if (!contratoTree) {
                            clienteTree.contratos.push(contrato);
                        }
                        // Senão, substituo pelo novo contrato
                        else {
                            contratoTree = contrato;
                        }
                    })
                }
            })
        }
    }
}
