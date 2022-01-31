import angular = require('angular');

import { CrmFornecedoresenvolvidos } from './../../Fornecedoresenvolvidos/factory';
import { CrmOrcamentos } from '../../Orcamentos/factory';
import { CrmAtcsFormHistoricoService } from '../../Atcshistorico/modal';
import { CrmFornecedoresenvolvidosFormService } from '../../Fornecedoresenvolvidos/modal';
import { Usuarios } from '../../../../usuarios/usuarios.service';
import { EOrcamentoStatus } from '../../Orcamentos/classes/eorcamentostatus';
import { FichaFinanceiraFornecedor } from './../Fichafinanceira/classes/fichafinanceira';
import { Orcamento } from '../../Orcamentos/classes/orcamento';
import { EFaturamentoTipo } from '../../Orcamentos/classes/efaturamentotipo';
import { EDescontoGlobalTipo } from '../../Fornecedoresenvolvidos/classes/edescontoglobaltipo';
import { NsCamposcustomizados } from '../../../Ns/Camposcustomizados/factory';
import { ConfiguracaoService } from '../../../../inicializacao/configuracao.service';
import { CommonsUtilsService } from '../../../Commons/utils/utils.service';
import { NsjAuth } from '@nsj/core';

export class CrmAtcsPreencherOrcamentoFornecedorController {

    /**
     * Injeção de dependencias da tela.
     */
    static $inject = [
        '$scope',
        'toaster',
        'entity',
        'parametros',
        'CrmFornecedoresenvolvidos',
        'CrmOrcamentos',
        'CrmAtcsFormHistoricoService',
        'CrmFornecedoresenvolvidosFormService',
        'CommonsUtilsService',
        'Usuarios',
        '$rootScope',
        'NsCamposcustomizados',
        'ConfiguracaoService'
    ];

    public EOrcamentoStatus = EOrcamentoStatus;
    public EFaturamentoTipo = EFaturamentoTipo;
    public EDescontoGlobalTipo = EDescontoGlobalTipo;

    /**
     * Define se a tela foi inicializada.
     */
    public inicializado: boolean = false;
    /**
     * Define se a tela está em processamento
     */
    public busy: boolean = false;
    /**
     * Define mensagem de processamento
     */
    public busyMensagem: string = 'Carregando...';
    /**
     * Define se a tela está em processamento de inicialização
     */
    public busyInicializacao: boolean = false;
    /**
     * Dados utilizados para apresentar ficha financeira por fornecedor
     */
    private dadosFicha: IDadosFichaFornecedor = null;
    /**
     * Id do fornecedor utilizando a tela
     */
    private fornecedorid: string;
    /**
     * Lista de entidades Ns/CamposCustomizados
     */
    public camposCustomizados: any[] = [];
    /**
     * Define se o municipio será exibido.
     */
    public exibirMunicipioSepultamento: boolean = false;

    constructor(
        public $scope: any,
        public toaster: any,
        /**
         * Entidade do atendimento comercial
         */
        public entity: any,
        /**
         * Parametros da tela
         */
        public parametros: any,
        /**
         * Service de fornecedores envolvidos
         */
        public CrmFornecedoresenvolvidos: CrmFornecedoresenvolvidos,
        /**
         * Service de orçamentos
         */
        public CrmOrcamentos: CrmOrcamentos,
        /**
         * Service de modal de histórico do atendimento
         */
        public CrmAtcsFormHistoricoService: CrmAtcsFormHistoricoService,
        /**
         * Service da modal de fornecedores envolvidos
         */
        public CrmFornecedoresenvolvidosFormService: CrmFornecedoresenvolvidosFormService,
        /**
         * Service de funções comuns
         */
        public CommonsUtilsService: CommonsUtilsService,
        /**
         * Service de usuários
         */
        public Usuarios: Usuarios,
        public $rootScope: angular.IRootScopeService,
        /**
         * Service de campos customizados
         */
        public NsCamposcustomizados: NsCamposcustomizados,
        /**
         * Service de configurações
         */
        public ConfiguracaoService: ConfiguracaoService

    ) {}

    /* Carregamento */
    $onInit() {
        this.inicializado = true;

        // Ativo busy de inicialização
        this.setBusy('Carregando informações do atendimento');

        // Atribuo id do fornecedor
        this.fornecedorid = this.parametros.fornecedor;

        // Defino se exibe município de sepultamento
        const configMunicipioSepultamento = this.ConfiguracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');
        if (configMunicipioSepultamento == '1') {
            this.exibirMunicipioSepultamento = true;
        }
        
        // Chamo função que carrega dados da tela;
        this.carregarDadosTela();      
    }

    /**
     * Verifica se possui permissão
     * @param chave 
     */
    temPermissao(chave: any): boolean {
        return this.Usuarios.temPermissao(chave);
    }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela() {

        try {
            // Busco dados da api
            let [arrDadosApi, camposCustomizados] = await Promise.all([
                this.CrmFornecedoresenvolvidos.getDadosFichaFinanceira(this.entity.negocio, '', true),
                this.getDadosCustomizados()
            ]);

            this.camposCustomizados = (<any[]>camposCustomizados);

            // Filtro pelos dados do fornecedor informado
            arrDadosApi = arrDadosApi.filter((dadoFilter) => {
                return dadoFilter.fornecedor == this.fornecedorid;
            });

            const arrFichas = FichaFinanceiraFornecedor.getFichasFinanceiras(arrDadosApi);

            // Monto linhas de orçamentos utilizados na visualização da tabela
            this.dadosFicha = null;

            if (arrFichas.length > 0) {
                this.montarDadosFichaFornecedor(arrFichas[0]);
            }
        } catch (error) {
            this.toaster.pop({
                type: 'error',
                title: 'Não foi possível buscar dados do atendimento, você será deslogado em 5 segundos...'
            });
            setTimeout(() => {
                NsjAuth.authService.logout();
            }, 5000);
        }

        // Desativo loading
        this.busyInicializacao = false;
        this.busy = false;
        this.reloadScope();
    }

    /**
     * Busca dados customizados do atendimento passado
     */
    private getDadosCustomizados(){
        return new Promise((resolve, reject) => {
            // Pego as chaves dos campos customizados do atendimento
            let arrChavesCamposAtc = Object.keys(this.entity.camposcustomizados);

            // Se não houver campos customizados, não mando requisição
            if (arrChavesCamposAtc.length == 0) {
                resolve([]);
                return;
            }

            // Monto filtros por campo customizado
            let filtros = {
                'campocustomizado': {}
            };
            
            arrChavesCamposAtc.forEach((campoID, index) => {
                filtros['campocustomizado'][index] = {
                    condition: 'eq',
                    value: campoID
                }
            });

            // Mando requisição
            this.NsCamposcustomizados.getAll(filtros, true).then((dados) => {
                // Filtro dados, pois o back-end não está filtrando id do campo customizado
                dados = (<any[]>dados).filter((campoFilter) => {
                    return arrChavesCamposAtc.indexOf(campoFilter.campocustomizado) > -1;
                });

                resolve(dados);
            }).catch((err) => {
                reject(err);
            });
        });
    }

    /**
     * Ativa o busy de processamento da tela.
     */
    private setBusy(mensagem = 'Carregando...'){
        this.busyMensagem = mensagem;
        this.busy = true;
    }

    /**
     * Atualiza a visualização do escopo tela
     */
    reloadScope() {
        this.$scope.$applyAsync();
    }

    /**
     * Monta dados utilizados para apresentar ficha financeira do fornecedor
     * @param ficha 
     */
    montarDadosFichaFornecedor(ficha: FichaFinanceiraFornecedor){
        const dadoFicha: IDadosFichaFornecedor = {
            ficha: ficha,
            linhasServicos: this.montarLinhasServicosTabela(ficha),
            linhasProdutos: this.montarLinhasProdutosTabela(ficha),
            acoes: []
        }

        // Busco o objeto referente aos dados
        this.dadosFicha = dadoFicha;
    }

    /**
     * Retorna lista de orçamentos de serviços para apresentar na tabela
     * @param ficha
     */
    montarLinhasServicosTabela(ficha: FichaFinanceiraFornecedor): ILinhaTabela[]{
        let arrOrcamentosServicos = ficha.getOrcamentosServicos();

        let arrDados: ILinhaTabela[] = [];

        // Adiciono lista de serviços
        if (arrOrcamentosServicos.length > 0) {
            arrDados.push({tipo: 'titulo', nome: 'Serviços', acoes: []});

            arrOrcamentosServicos.forEach((orcamentoForeach) => {
                const linha: ILinhaTabela = {
                    tipo: 'valor',
                    orcamento: orcamentoForeach,
                    orcamento_old: angular.copy(orcamentoForeach),
                    acoes: []
                };
                
                arrDados.push(linha);
            })
        }

        return arrDados;
    }

    /**
     * Retorna lista de orçamentos de produtos para apresentar na tabela
     * @param ficha
     */
    montarLinhasProdutosTabela(ficha: FichaFinanceiraFornecedor): ILinhaTabela[]{
        let arrOrcamentosMercadorias = ficha.getOrcamentosMercadorias();

        let arrDados: ILinhaTabela[] = [];

        // Adiciono lista de mercadorias
        if (arrOrcamentosMercadorias.length > 0) {
            arrDados.push({tipo: 'titulo', nome: 'Produtos', acoes: []});
            arrOrcamentosMercadorias.forEach((orcamentoForeach) => {
                const linha: ILinhaTabela = {
                    tipo: 'valor',
                    orcamento: orcamentoForeach,
                    orcamento_old: angular.copy(orcamentoForeach),
                    acoes: []
                };
                
                arrDados.push(linha);
            })
        }

        return arrDados;
    }

    /**
     * Verifica se a ficha pode ser editada.
     * Retorna true caso o status do orçamento seja Aberto
     * @param ficha
     */
    podeEditarFicha(ficha: FichaFinanceiraFornecedor): boolean {
        return ficha.status.status == EOrcamentoStatus.osAberto;
    }

    /**
     * Verifica se pode editar um campo do orçamento
     * @param ficha 
     * @param linha 
     * @param campo 
     */
    podeEditarCampoOrcamento(ficha: FichaFinanceiraFornecedor, linha: ILinhaTabela, campo: string): boolean {
        // Verifico se ficha pode ser editada
        if (!this.podeEditarFicha(ficha)) {
            return false;
        }

        // Verifico se tem permissão de alteração
        if (!this.temPermissao('crm_orcamentos_put')) {
            return false;
        }

        // Se for um dos campos abaixo, deixo editar
        if (
            [
                'valorunitario', 'quantidade', 'desconto', 'valorreceber', 'faturamentotipo', 'fornecedorterceirizado'
            ].indexOf(campo) > -1
        ) {
            return true;
        }

        // O custo só pode ser alterado caso o fornecedor possua estabelecimento
        if (campo == 'custo') {
            const estabelecimento = ficha.fornecedorEnvolvido.fornecedor.estabelecimentoid.estabelecimento;
            return estabelecimento != null && estabelecimento != '';
        }

        // O campo descrição só pode ser alterado, se estiver marcado que o orçamento possui descrição manual
        if (campo == 'descricao') {
            return linha.orcamento.descricaomanual;
        }
    }

    /**
     * Atualiza os descontos da ficha financeira
     * @param ficha 
     * @param descontoglobalunitario 
     * @param descontoglobaldirefenca 
     * @param orcamentodescontodiferenca 
     */
    private atualizarDescontosFicha(ficha: FichaFinanceiraFornecedor, descontoglobalunitario: number, descontoglobaldirefenca: number, orcamentodescontodiferenca: string){
        ficha.arrOrcamentos.forEach((orcamentoForeach) => {
            if (ficha.fornecedorEnvolvido.possuidescontoglobal) {
                if (orcamentoForeach.orcamento == orcamentodescontodiferenca) {
                    orcamentoForeach.descontoglobal = descontoglobaldirefenca;
                } else {
                    orcamentoForeach.descontoglobal = descontoglobalunitario;
                }
            } else {
                orcamentoForeach.descontoglobal = 0;
            }

            if (!ficha.fornecedorEnvolvido.possuidescontoparcial) {
                orcamentoForeach.desconto = 0;
            }
        });
    }

    /**
     * Verifica se pode realizar alterações de valor na ficha
     * @param ficha 
     * @param alterandoDescontoGlobal 
     */
    private podeAlterarCampoValor(ficha: FichaFinanceiraFornecedor, alterandoDescontoGlobal: boolean = false): boolean {
        // Busco totais
        let totalDescontos = ficha.getTotal('desconto');
        let totalDescontoGlobal = ficha.getTotal('descontoglobal');
        const totalOrcamento = ficha.getTotal('valor');

        if (!ficha.fornecedorEnvolvido.possuidescontoparcial) {
            totalDescontos = 0;
        }

        if (!ficha.fornecedorEnvolvido.possuidescontoglobal) {
            totalDescontoGlobal = 0;
        } else if (alterandoDescontoGlobal) {
            if (ficha.fornecedorEnvolvido.descontoglobaltipo == EDescontoGlobalTipo.dgtValor) {
                totalDescontoGlobal = ficha.fornecedorEnvolvido.descontoglobal;
            } else {
                totalDescontoGlobal = (totalOrcamento / 100) * ficha.fornecedorEnvolvido.descontoglobal;
            }
        }
        
        return (totalOrcamento >= (totalDescontos + totalDescontoGlobal));
    }

    /**
     * Quando valor de uma das propriedades do orçamento for alterado
     * @param ficha 
     * @param linha 
     * @param campo 
     */
    onCampoOrcamentoChange(ficha: FichaFinanceiraFornecedor, linha: ILinhaTabela, campo: string){
        if (this.podeEditarCampoOrcamento(ficha, linha, campo)) {
            // Verifico se houve mudanças
            let houveMudanca = false;

            houveMudanca = (linha.orcamento.descricao != linha.orcamento_old.descricao)
                || (linha.orcamento.custo != linha.orcamento_old.custo)
                || (linha.orcamento.valorunitario != linha.orcamento_old.valorunitario)
                || (linha.orcamento.quantidade != linha.orcamento_old.quantidade)
                || (linha.orcamento.desconto != linha.orcamento_old.desconto)
                || (linha.orcamento.valorreceber != linha.orcamento_old.valorreceber)
                || (linha.orcamento.faturamentotipo != linha.orcamento_old.faturamentotipo)
                || (linha.orcamento.fornecedorterceirizado != linha.orcamento_old.fornecedorterceirizado);

            if (!houveMudanca) {
                return;
            }

            // Se está alterando algum campo de valor e o desconto fica maior que o orçamento, apresento mensagem de erro
            if (['valorunitario', 'quantidade', 'desconto'].indexOf(campo) > -1 && !this.podeAlterarCampoValor(ficha)) {
                linha.orcamento.valorunitario = linha.orcamento_old.valorunitario;
                linha.orcamento.quantidade = linha.orcamento_old.quantidade;
                linha.orcamento.desconto = linha.orcamento_old.desconto;

                this.toaster.pop({
                    type: 'error',
                    title: 'O orçamento total não pode ser inferior a soma dos descontos.'
                });

                return;
            }

            this.setBusy('Atualizando orçamento...');
            
            this.CrmOrcamentos.put(linha.orcamento.getDados()).then((retornoApi: any) => {
                // Atualizo objeto de orçamento de verificação de mudanças
                linha.orcamento_old = angular.copy(linha.orcamento);

                // Atualizo desconto dos itens, caso esteja configurado
                const descontoglobalunitario = retornoApi.descontoglobalunitario ? parseFloat(retornoApi.descontoglobalunitario) : 0;
                const descontoglobalresto = retornoApi.descontoglobalresto ? parseFloat(retornoApi.descontoglobalresto) : 0;
                const descontoglobalrestoorcamento = retornoApi.descontoglobalrestoorcamento || '';
                this.atualizarDescontosFicha(ficha, descontoglobalunitario, descontoglobalresto, descontoglobalrestoorcamento);

                // Atualizo informações
                ficha.atualizarInformacoesGerais();

                this.toaster.pop({
                    type: 'success',
                    title: 'Orçamento atualizado com sucesso.'
                });
            })
            .catch((error) => {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            })
            .finally(() => {
                this.busy = false;
                this.reloadScope();
            });
        }
    }


    /**
     * Retorna data formatada com data e hora
     * @param data 
     */
    getDataFormatada(data: Date, formato: string = 'DD/MM/YYYY'): string {
        if (data != null) {
            return this.CommonsUtilsService.getDataFormatada(data.toISOString(), formato);
        } else {
            return '';
        }
    }

    getFaturamentoDescricao(faturamentotipo: EFaturamentoTipo): string {
        return Orcamento.getFaturamentoDescricao(faturamentotipo);
    }

    /**
     * Define se apresenta opção de tipo de faturamento
     * @param ficha 
     * @param faturamentotipo 
     */
    apresentarTipoFaturamento(ficha: FichaFinanceiraFornecedor, faturamentotipo: EFaturamentoTipo): boolean {
        const arrTiposFaturamentos: EFaturamentoTipo[] = [EFaturamentoTipo.ftNaoFaturar];
        const fornecedor = ficha.fornecedorEnvolvido.fornecedor;
        const estabelecimento = ficha.fornecedorEnvolvido.fornecedor.estabelecimentoid.estabelecimento;
        const fornecedorEstabelecimento = (estabelecimento != null && estabelecimento != '');

        if (this.entity.possuiseguradora) {
            // Se o fornecedor é terceirizado(não possui estabelecimento)
            if (!fornecedorEstabelecimento) {
                if (!fornecedor.esperapagamentoseguradora) {
                    arrTiposFaturamentos.push(EFaturamentoTipo.ftFaturarSeguradoraSemNFS);
                }
            } else {
                arrTiposFaturamentos.push(EFaturamentoTipo.ftFaturarSeguradoraComNFS);
            }
        } else {
            arrTiposFaturamentos.push(EFaturamentoTipo.ftFaturarClienteComNFS);
        }

        return arrTiposFaturamentos.indexOf(faturamentotipo) > -1;
    }
}

/**
 * Linha de dados representando serviços, produtos ou titulos na ficha financeira.
 */
interface ILinhaTabela {
    tipo: 'titulo' | 'valor';
    nome?: string;
    orcamento?: Orcamento;
    /**
     * Utilizado para validar alterações no orçamento
     */
    orcamento_old?: Orcamento;
    acoes: IDadosFichaFornecedorAcao[];
}

interface IDadosFichaFornecedor {
    ficha: FichaFinanceiraFornecedor;
    linhasServicos: ILinhaTabela[];
    linhasProdutos: ILinhaTabela[];
    acoes: IDadosFichaFornecedorAcao[];
}

interface IDadosFichaFornecedorAcao{
    nome: string;
    icone?: string;
    funcao?: any
}
