import angular = require('angular');
import * as moment from 'moment';
import { CrmBuscaPropostaItemService } from '../Propostasitens/proposta-item-service';
// import { NsFormaspagamentos } from '@MDA/Ns/Formaspagamentos/factory';
// import { ServicosPessoasmunicipios } from '@MDA/Servicos/Pessoasmunicipios/factory';
// import { CrmResponsabilidadesfinanceiras } from '@MDA/Crm/Responsabilidadesfinanceiras/factory';
import { NsFormaspagamentos } from './../../Ns/Formaspagamentos/factory';
import { ServicosPessoasmunicipios } from './../../Servicos/Pessoasmunicipios/factory';
import { CrmResponsabilidadesfinanceiras } from './../Responsabilidadesfinanceiras/factory';
import { 
    IResponsavelFinanceiro, IPropostaItem, IPropostasItemFamilia, IPropostasItemFuncao, IOrcamento, 
    IResponsabilidadefinanceira 
} from './interfaces';
import { ISecaoController } from './classes/isecaocontroller';

export class CrmAtcsContratantesController {

    static $inject = [
        '$scope',
        'toaster',
        'entity',
        'CrmAtcs',
        'utilService',
        '$http',
        'nsjRouting',
        'moment',
        'CrmBuscaPropostaItemService',
        'NsFormaspagamentos',
        'ServicosPessoasmunicipios',
        'CrmResponsabilidadesfinanceiras',
        'secaoctrl'
    ];

    public busyBtnContratantes: boolean = false;
    public busyContratantes: boolean = false;
    private busyInicializacao: boolean = false;
    public loading_deferred: any = null;
    public contratantes: any;
    public lstPropostasItens: any = null;
    public myList: any = null;
    public possuiOrcamentos: boolean = false;
    public possuiOrcamentosAbertos: boolean = false;
    public inicializado: boolean = false;
    public responsabilidadesfinanceiras: any[] = [];

    // NOVO
    /**
     * Soma do valor dos serviços de todos os contratantes
     */
    private totalAFaturar: number = 0;

    /**
     * Soma do valor dos serviços dos contratantes com contrato gerado
     */
    private totalFaturado: number = 0;

    /**
     * Diferença do Total a faturar pelo Total faturado
     */
    private totalNaoFaturado(): number {
        return this.totalAFaturar - this.totalFaturado;
    }

    private arrResponsabilidadesfinanceiras: IResponsabilidadefinanceira[] = [];
    // private arrPropostasitens: IPropostaItem[] = [];
    // private arrPropostasitensfamilias: IPropostasItemFamilia[] = [];
    // private arrPropostasitensfuncoes: IPropostasItemFuncao[] = [];
    private arrOrcamentos: IOrcamento[] = [];
    /**
     * Variavel que contém as informações presentes na tela, como nome do contratante, serviços, etc.
     */
    private arrContratantesTela: IContratanteTela[] = [];

    /**
     * Faz o controle se o accordion de contas a receber já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela(){
        // Reseto variaveis
        this.arrResponsabilidadesfinanceiras = [];
        // this.arrPropostasitens = [];
        // this.arrPropostasitensfamilias = [];
        // this.arrPropostasitensfuncoes = [];
        this.arrOrcamentos = [];
        this.arrContratantesTela = [];

        this.busyContratantes = true;

        // Busco responsabilidades financeiras e propostasitens
        [
            this.arrResponsabilidadesfinanceiras,
            // this.arrPropostasitens
        ] = await Promise.all([
            this.getResponsabilidadesFinanceirasFromAPI(),
            // this.getPropostasitensFromApi(this.entity.negocio, this.entity.propostas[0].proposta)
        ]);

        this.arrOrcamentos = await this.getOrcamentosAtc(this.entity.negocio);

        this.possuiOrcamentos = false;

        this.possuiOrcamentosAbertos = false;

        this.arrOrcamentos.forEach((orcamento) => {
            if(orcamento.faturamentotipo > 1){
                this.possuiOrcamentos = true;
            }
            if(orcamento.status < 2){
                this.possuiOrcamentosAbertos = true;
            }
        })

        // Organizo dados da tela, baseado nas informações buscadas nessa função.
        this.organizarDadosTela();

        // Calculo valores totais
        this.calcularTotais();

        this.busyContratantes = false;
        this.busyInicializacao = false;

        //Uma vez que o accordion foi carregado, seto a variável de controle para true
        this.accordionCarregado = true;

        this.reloadScope();
    }

    /**
     * Organiza as informações buscadas na função 'carregarDadosTela' para funcionar no modelo de dados da tela
     */
    private organizarDadosTela() {
        for (let index = 0; index < this.entity.responsaveisfinanceiros.length; index++) {
            const responsavelfinanceiro: IResponsavelFinanceiro = this.entity.responsaveisfinanceiros[index];
            
            // Monto objeto do contratante
            const contratanteTela: IContratanteTela = {
                responsavelfinanceiro: responsavelfinanceiro.responsavelfinanceiro.cliente,
                nome: responsavelfinanceiro.responsavelfinanceiro.nomefantasia,
                contratogerado: false,
                formapagamento: responsavelfinanceiro.responsavelfinanceiro.formapagamentoguid,
                municipioprestacao: null,
                gerandocontrato: false,
                excluindocontrato: false,
                servicos: [],
                totalservicos: 0
            };

            // Preencho a lista de serviços do contratante
            this.arrResponsabilidadesfinanceiras.forEach((responsabilidadefinanceira) => { //para cada responsabilidade...

                responsabilidadefinanceira.responsabilidadesfinanceirasvalores.forEach((responsabilidadefinanceiravalor) => { //para cada valor...

                    if (responsabilidadefinanceiravalor.responsavelfinanceiro == contratanteTela.responsavelfinanceiro) {
                        // Só adiciono itens com valor
                        if (responsabilidadefinanceiravalor.valorpagar <= 0) {
                            return;
                        }

                        // Declaro o serviço, somente com as informações utilizas em tela
                        const servico: IServicoContrato = {
                            nome: '',
                            valor: responsabilidadefinanceiravalor.valorpagar
                        }

                        let orcamentoCorrespondente = this.arrOrcamentos.find(orcamento => {
                            return orcamento.orcamento == responsabilidadefinanceira.orcamento;
                        });
                        servico.nome = orcamentoCorrespondente.descricao;

                        // Adiciono serviço à lista de serviços do contratante, incrementando o valor total
                        contratanteTela.servicos.push(servico);
                        contratanteTela.totalservicos += servico.valor;
                        contratanteTela.geranotafiscal = responsabilidadefinanceira.geranotafiscal;
                        contratanteTela.contratogerado = (contratanteTela.contratogerado) ? 
                            true : responsabilidadefinanceiravalor.contrato != null;
                    }
                });
            });

            // Adiciono contratante à lista de contratantes
            this.arrContratantesTela.push(contratanteTela);
        }
    }

    /**
     * Calcula os valores faturados e a faturar
     */
    private calcularTotais() {
        this.totalAFaturar = 0;
        this.totalFaturado = 0;

        this.arrContratantesTela.forEach((contratante) => {
            this.totalAFaturar += contratante.totalservicos;
            if (contratante.contratogerado) {
                this.totalFaturado += contratante.totalservicos;
            }
        })
    }

    /**
     * Busca responsabilidades financeiras da API
     */
    getResponsabilidadesFinanceirasFromAPI(): Promise<IResponsabilidadefinanceira[]> {
        let constructors = {
            'negocio': this.entity.propostas && this.entity.propostas[0].negocio,
        };
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_responsabilidadesfinanceiras_index', constructors, true)
            }).then(async (response: any) => {
                const arrDados: IResponsabilidadefinanceira[] = response.data;
                arrDados.forEach((responsabilidadefinanceira) => {
                    responsabilidadefinanceira.responsabilidadesfinanceirasvalores.forEach((responsabilidadefinanceiravalor) => {
                        responsabilidadefinanceiravalor.valorpagar = parseFloat(responsabilidadefinanceiravalor.valorpagar.toString());
                    });
                });
                resolve(arrDados);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Busca propostasitens da API
     */
    getPropostasitensFromApi(atc, proposta: string): Promise<IPropostaItem[]> {
        return new Promise((resolve, reject) => {
            this.CrmBuscaPropostaItemService.carregaPropostasitens(proposta, atc).then((dados: IPropostaItem[]) => {
                resolve(dados);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    /**
     * Busca propostas itens famílias da API
     * @param propostaitem 
     */
    private getPropostasitensfamiliasFromApi(propostaitem: string): Promise<IPropostasItemFamilia[]> {
        return new Promise((resolve, reject) => {
            this.CrmBuscaPropostaItemService.carregaPropostasitensFamilias(propostaitem).then((dados: IPropostasItemFamilia[]) => {
                resolve(dados);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    /**
     * Carrega orçamentos
     * @param propostaitem 
     */
    private getOrcamentosAtc(atc): Promise<IOrcamento[]>{ //propostaitem: string, arrPropostaitem: string[] = []
        return new Promise((resolve, reject) => {
            this.getOrcamentosAtcRequest(atc).then((dados: IOrcamento[]) => {
                resolve(dados);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    /**
     * Faz Request
     * @param atc 
     */
     getOrcamentosAtcRequest(atc){
        let constructors = {};
        let paramFilters: any = {
            'atc': atc,
            offset: ''
        }
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_orcamentos_index', angular.extend({}, constructors, paramFilters), true, true),
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;
    
    constructor(
        public $scope: any,
        public toaster: any,
        public entity: any,
        public entityService: any,
        public utilService: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public moment: any,
        public CrmBuscaPropostaItemService: CrmBuscaPropostaItemService,
        public NsFormaspagamentos: NsFormaspagamentos,
        public ServicosPessoasmunicipios: ServicosPessoasmunicipios,
        public CrmResponsabilidadesfinanceiras: CrmResponsabilidadesfinanceiras,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController
    ) {
        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
            }

            //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar dados do accordion
            if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
                // Ativo busy de inicialização
                this.busyContratantes = true;
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                this.busyInicializacao = true;
                // Chamo função que carrega dados da tela;
                this.carregarDadosTela();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }
    }

    /* Carregamento */
    Init() {
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;

            this.$scope.$on('crm_contratos_responsabilidades_submitted', (event: any, args: any) => {
                this.busyContratantes = true;
                this.carregarDadosTela();
            });

            this.$scope.$on('crm_contratos_refresh', (event: any, args: any) => {
                this.busyContratantes = true;
                this.carregarDadosTela();
            });
        }
    }

    async gerarContrato(contratante: IContratanteTela) {
        if (!contratante.servicos || contratante.servicos.length == 0) {
            return; //melhorar validação
        }
        
        let atcId = this.entity.negocio;
        let dataToSend = {
            'formapagamento': contratante.formapagamento,
            'municipioprestacao': contratante.municipioprestacao,
            'contratante': contratante.responsavelfinanceiro
        };

        dataToSend.municipioprestacao.pessoamunicipio = dataToSend.municipioprestacao.pessoamunicipio ? 
                                                        dataToSend.municipioprestacao.pessoamunicipio :
                                                        dataToSend.municipioprestacao.municipioprestacao;
        delete dataToSend.municipioprestacao.municipioprestacao;
        
        this.busyBtnContratantes =  true;
        contratante.gerandocontrato = true;
        
        const response = await this.$http({
            method: 'POST',
            data: dataToSend,
            url: this.nsjRouting.generate('crm_atcs_gera_contrato', { id: atcId }),
        }).then((result) => {
            this.busyBtnContratantes = false;
            contratante.gerandocontrato = false;
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao gerar contrato!'
            });
            this.busyContratantes = true;
            this.carregarDadosTela();
        })
        .catch((error) => {
            this.busyBtnContratantes = false;
            contratante.gerandocontrato = false;
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao gerar contrato!',
                body: error.data.message !== undefined ? error.data.message : error, 
                bodyOutputType: 'trustedHtml'
            });
        });
    }

    excluirContrato(contratante: IContratanteTela) {
        //financas_contratos_exclui_contrato_pagamento
        this.busyBtnContratantes = true; 
        contratante.excluindocontrato = true;

        const dataToSend = {
            'contratante': contratante.responsavelfinanceiro,
        }
        const response = this.$http({
            method: 'POST',
            data: dataToSend,
            url: this.nsjRouting.generate('crm_atcs_exclui_contrato', { id: this.entity.negocio }),
        }).then((result) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir contrato!'
            });
            this.busyBtnContratantes = false;
            contratante.excluindocontrato = false;
            contratante.contratogerado = false;
            this.calcularTotais();
        })
        .catch((error) => {
            this.busyBtnContratantes = false;
            contratante.excluindocontrato = false;
            this.toaster.pop({
                type: 'error',
                title: error//'Erro ao gerar contrato!'
            });
        });
    }

    formularioInvalido(contratante: IContratanteTela){
        return !contratante.municipioprestacao || !contratante.formapagamento;
    }

    reloadScope() {
        this.$scope.$applyAsync();
    }

    isbusyContratantes() {
        return this.busyContratantes;
    }

    isBusyBtn() {
        return this.busyBtnContratantes
    }
}

// Interfaces da tela
interface IContratanteTela {
    responsavelfinanceiro: string;
    nome: string;
    contratogerado: boolean;
    formapagamento: string;
    municipioprestacao: any;
    servicos: IServicoContrato[];
    totalservicos: number;
    geranotafiscal: boolean; //indica se a responsabilidade deste contratante gerará nota fiscal
    /**
     * Usado para identificar que a ação de gerar contrato está em andamento
     */
    gerandocontrato: boolean;
    /**
     * Usado para identificar que a ação de excluir contrato está em andamento
     */
    excluindocontrato: boolean;
}
interface IServicoContrato {
    nome: string;
    valor: number;
}
