import angular = require('angular');
import { Tree } from '../../Commons/tree';
import { CrmBuscaPropostaItemService } from '../Propostasitens/proposta-item-service';
// import { CrmResponsabilidadesfinanceiras } from '@MDA/Crm/Responsabilidadesfinanceiras/factory';
import { CrmResponsabilidadesfinanceiras } from './../Responsabilidadesfinanceiras/factory';
import { CrmContratoPaganteAcoesModalService } from './contrato-mais-pagante-acoes-modal';
export class CrmContratoMaisPaganteModalController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        'Tree',
        '$scope',
        '$filter',
        'nsjRouting',
        '$http',
        'CrmBuscaPropostaItemService',
        'CrmResponsabilidadesfinanceiras',
        'CrmContratoPaganteAcoesModalService',
        'NsFornecedores',
        '$rootScope'
    ];

    public action: string;
    public form: any;
    public submitted: boolean = false;

    public expanding_property: treeExpand;
    public col_defs: any;
    public treetest = [];

    public locaisOrcamentosPreencherInfo: any;
    public busyContratoMaisPaganteModal: boolean = false;
    novaTree: any[];

    public orcamentoRealTotal: number = 0;
    public descontoTotal: number = 0;
    public acrescimoTotal: number = 0;
    public orcamentoTotal: number = 0;
    //Variável que faz o controle da exibição do alert na tela
    public msgErroDesconto: boolean = false;

    public responsaveisFinanceiros: any;
    public lstPropostasItens: any;
    public nodesOrcamentos: any;
    public responsaveisFinanceirosAssociados: any = [];

    public modoDivisao = [
        { tipo: 0, valor: 'semDivisao', label: 'Sem divisão' },
        { tipo: 1, valor: 'divisaoIgual', label: 'Divisão igual' },
        { tipo: 2, valor: 'divisaoPercentual', label: 'Divisão percentual' },
        { tipo: 3, valor: 'divisaoValor', label: 'Divisão por valor' },
    ]

    responsabilidadesAssociadas: any = {};

    // Variaveis contendo os dados buscados das API's para o funcionamento da tela
    arrResponsabilidadesfinanceiras: IResponsabilidadefinanceira[] = [];
    arrPropostasitens: IPropostaItem[] = [];
    arrOrcamentos: IOrcamento[] = [];
    arrFornecedores: IFornecedor[] = [];
    listaOrcamentos: IOrcamento[] = [];

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public tree: Tree,
        public $scope: any,
        public $filter: any,
        public nsjRouting: any,
        public $http: any,
        public CrmBuscaPropostaItemService: CrmBuscaPropostaItemService,
        public CrmResponsabilidadesfinanceiras: CrmResponsabilidadesfinanceiras,
        public CrmContratoPaganteAcoesModalService: CrmContratoPaganteAcoesModalService,
        public NsFornecedores: any,
        public $rootScope,
    ) {
    }

    $onInit() {
        this.carregarDadosTela();
        this.initColDef();
        this.getResponsaveisFinanceiros();
        this.setActionColDef();
        this.calcularValorTotalResponsaveisFinanceiros();
    }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela(){
        this.busyContratoMaisPaganteModal = true;

        // Reseto variaveis
        this.arrResponsabilidadesfinanceiras = [];
        this.arrPropostasitens = [];
        this.arrOrcamentos = [];
        this.arrFornecedores = [];
        // Busco responsabilidades financeiras e propostasitens
        [
            this.arrResponsabilidadesfinanceiras,
            this.arrPropostasitens,
            this.listaOrcamentos
        ] = await Promise.all([
            this.getResponsabilidadesFinanceirasFromAPI(),
            this.getPropostasitensFromApi(this.entity.obj.negocio, this.entity.obj.propostas[0].proposta),
            this.getOrcamentosPorAtc(this.entity.obj.negocio)
        ]);

        let dadosAPIs: {
            arrPromises: Promise<any>[],
            arrTipoInfo: ('fornecedor')[],
            arrRetornoApi: any[]
        } = {
            arrPromises: [], // Promisses de busca por família, função, orçamento e fornecedor
            arrTipoInfo: [], // Tipo de informação guardado no indice: Familia, função, orçamento ou fornecedor
            arrRetornoApi: [] // Dados retornados das requisições
        };

        let arrOrcamentosFilter: string[] = []; // Array de filtro de propostaitem para buscar orçamentos
        let arrFornecedoresIds: string[] = []; // Array para guardar os fornecedores que estou buscando
        this.arrPropostasitens.forEach((propostaitem) => {
            // Adiciono promise de fornecedor, caso o mesmo ainda não tenha sido buscado e o fornecedor espere pagamento da seguradora
            if (propostaitem.fornecedor != null && propostaitem.fornecedor.fornecedor != null &&
                propostaitem.fornecedor.esperapagamentoseguradora && arrFornecedoresIds.indexOf(propostaitem.fornecedor.fornecedor) < 0) {
                dadosAPIs.arrPromises.push(this.getFornecedorFromApi(propostaitem.fornecedor.fornecedor));
                dadosAPIs.arrTipoInfo.push('fornecedor');
                dadosAPIs.arrRetornoApi.push([]);
                // Adiciono ao array 'arrFornecedoresIds' para não buscar o mesmo fornecedor novamente, 
                // caso esteja selecionado em outra propostaitem
                arrFornecedoresIds.push(propostaitem.fornecedor.fornecedor);
            }
            arrOrcamentosFilter.push(propostaitem.propostaitem);
        })

        // Faço todas as requisições de propostasitensfamilias e propostasitensfuncoes ao mesmo tempo, para otimizar
        dadosAPIs.arrRetornoApi = await Promise.all(dadosAPIs.arrPromises);

        // Distribuo as informações para as variaveis correspondentes
        dadosAPIs.arrTipoInfo.forEach((tipo, index) => {
            switch (tipo) {
                case 'fornecedor': {
                    this.arrFornecedores = this.arrFornecedores.concat(dadosAPIs.arrRetornoApi[index]);
                    break;
                }
            }
        });

        // Organizo dados da tela, baseado nas informações buscadas nessa função.
        this.organizarDadosTela();
    }

    /**
     * Busca responsabilidades financeiras da API
     */
    private getResponsabilidadesFinanceirasFromAPI(): Promise<IResponsabilidadefinanceira[]> {
        let constructors = {
            'negocio': this.entity.obj.negocio
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
    private getPropostasitensFromApi(atc, proposta: string): Promise<IPropostaItem[]> {
        return new Promise((resolve, reject) => {
            this.CrmBuscaPropostaItemService.carregaPropostasitens(proposta, atc).then((dados: IPropostaItem[]) => {
                resolve(dados);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    /**
     * Carrega orçamentos por atc
     * @param propostaitem 
     */
     carregaOrcamentoPorAtc(atc: string){
        let constructors = {};
        let paramFilters: any = {
            atc: atc,
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
     * Busca orcamentos da API
     * @param propostaitem 
     */
     private getOrcamentosPorAtc(atcs: string): Promise<IOrcamento[]> {
        return new Promise((resolve, reject) => {
            this.carregaOrcamentoPorAtc(atcs).then((dados: IOrcamento[]) => {
                // Faço o parse dos dados de valor
                dados.forEach((orcamento) => {
                    orcamento.valor          = parseFloat((orcamento as any).valor);
                    orcamento.acrescimo      = parseFloat((orcamento as any).acrescimo);
                    orcamento.desconto       = parseFloat((orcamento as any).desconto);
                    orcamento.descontoglobal = orcamento.descontoglobal != null ? parseFloat((orcamento as any).descontoglobal) : 0;
                    orcamento.valorreceber   = parseFloat((orcamento as any).valorreceber);
                });
                
                resolve(dados);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    /**
     * Busca fornecedor da API
     * @param fornecedor 
     */
    private getFornecedorFromApi(fornecedor: string): Promise<IFornecedor[]> {
        return new Promise((resolve, reject) => {
            this.NsFornecedores.get(fornecedor).then((dados: IFornecedor[]) => {
                resolve(dados);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    isBusy() {
        return this.busyContratoMaisPaganteModal;
    }

    isValid() {
        const totalRestante = this.calcularTotal(this.treetest, 'totalRestante');
        return totalRestante === 0;
    }

    salvaResponsabilidadeFinanceira() {
        if (this.isValid()) {
            this.busyContratoMaisPaganteModal = true;
            const constructors = {
                'atc': this.entity.obj.propostas[0].negocio,
            };

            let obj = {
                'atc': this.entity.obj.propostas[0].negocio,
                'responsabilidadesfinanceiras': []
            }
            this.lstPropostasItens.forEach(element => {
                element.children.forEach(filho => {
                    const respFinanceirasValores = filho.obj.responsabilidadesfinanceiras.map(resp => {
                        return {
                            'responsabilidadefinanceira': resp.responsabilidadefinanceira?.responsabilidadefinanceira ? 
                                resp.responsabilidadefinanceira.responsabilidadefinanceira : resp.responsabilidadefinanceira,
                            'responsabilidadefinanceiravalor': resp.responsabilidadefinanceiravalor,
                            'responsavelfinanceiro': resp.responsavelfinanceiro,
                            'valorpagar': parseFloat(resp.valorpagar)
                        }
                    });

                    let novo = {
                        'responsabilidadefinanceira': filho.obj.responsabilidadefinanceira?.responsabilidadefinanceira ? 
                            filho.obj.responsabilidadefinanceira.responsabilidadefinanceira : filho.obj.responsabilidadefinanceira,
                        'responsabilidadesfinanceirasvalores': respFinanceirasValores,
                        'tipodivisao': filho.tipodivisao.tipo,
                        'valorservico': filho.orcamentoValor,
                        'negocio': this.entity.obj.propostas[0].negocio,
                        'orcamento': filho.orcamento.orcamento,
                        'faturamentotipo': filho.orcamento.faturamentotipo
                    }

                    if (element.obj.propostasitensfamilias?.length > 0 && filho.obj.propostaitemfamilia !== undefined && !filho.obj.orcamento) {
                        novo['propostaitemfamilia'] = filho._id_

                    } else if (element.obj.propostasitensfuncoes?.length > 0 && filho.obj.propostaitemfuncao !== undefined && !filho.obj.orcamento) {
                        novo['propostaitemfuncao'] = filho._id_
                    }

                    obj.responsabilidadesfinanceiras.push(novo);
                });
            });

            this.$http({
                method: 'POST',
                url: this.nsjRouting.generate('crm_responsabilidadesfinanceiras_salvar_lote', angular.extend({}, constructors, {}), true),
                data: angular.copy(obj),

            }).then((response: any) => {
                this.busyContratoMaisPaganteModal = false;
                this.close();
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao editar responsabilidades financeiras.'
                });

                this.$rootScope.$broadcast('crm_contratos_responsabilidades_submitted', response);
            }).catch((erro: any) => {
                this.busyContratoMaisPaganteModal = false;
                this.close();
                this.toaster.pop({
                    type: 'error',
                    title: erro.data?.message ? erro.data.message : erro
                });
            });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Total pago diferente do esperado.'
            });
        }
    }

    getResponsabilidadesFinanceiras() {
        let constructors = {
            'negocio': this.entity.obj.propostas[0].negocio,
        };

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_responsabilidadesfinanceiras_index', constructors, true)
            }).then(async (response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /* tree */

    /* --- */
    initColDef() {

        this.expanding_property = {
            field: 'nome',
            displayName: 'Serviços',
            sortable: true,
            filterable: true,
            cellTemplate: `
                <span ng-bind="row.branch.nome || '-'"></span>`,
        }

        /* carrega as colunas */
        this.col_defs = [
            {
                field: 'orcamento',
                displayName: 'Valor a ser Pago',
                cellTemplate: `
                    <div ng-class="{'fix-cell-align':!cellTemplateScope.temInputEditavel(row.branch.tipodivisao)}" class="container-flex">
                        <div ng-if="cellTemplateScope.temInputEditavel(row.branch.tipodivisao)">
                            <div class="container-icon">
                                <i class="fas fa-info-circle blue"></i>
                                <nsj-tooltip tooltip-position="top">
                                    <p style="white-space: nowrap">
                                        <span class="fix-tooltip">
                                            <i class="fas fa-check text-success" ></i>
                                            Total pago: <span class="tooltip-info text-success">{{row.branch.totalPago | currency : 'R$' : 2}}</span>
                                        </span> 
                                        <span class="fix-tooltip">
                                            <i class="fas fa-times text-warning"></i>
                                            Total a pagar: <span class="tooltip-info text-warning">{{row.branch.totalRestante | currency : 'R$' : 2}}<span>
                                        </span>
                                    </p>
                                </nsj-tooltip>
                            </div>
                        </div>
                        <span 
                            ng-if="row.branch.tipo != 'propostaitem'"
                            ng-class={'text-success':row.branch.orcamentoValor,'text-bold':row.branch.children.length}
                            ng-bind="(row.branch.orcamentoValor | currency : 'R$' : 2) ">
                        </span>
                    <div>
                    `
                ,
                cellTemplateScope: {
                    temInputEditavel: (tipodivisao) => {
                        return this.temInputEditavel(tipodivisao);
                    }
                }
            },
            {
                field: 'tipodivisao',
                displayName: 'Modo de divisão',
                cellTemplate: `
                    <nsj-button 
                        ng-if="false"
                        icon="fas fa-list" 
                        color="warning"
                        size="xs"
                        ng-if="row.branch.children.length > 0" 
                        ng-click="cellTemplateScope.abrirAplicarEmMassaModal(row.branch)"
                        > Aplicar em Massa 
                    </nsj-button>
                    <select
                        ng-if="row.branch.level!=1"
                        class="form-control"
                        name="modoDivisao"
                        id="modoDivisao" 
                        ng-change="cellTemplateScope.escolhaDivisao(row.branch,true)"
                        ng-disabled=" row.branch.orcamentoValorreceber < 0"
                        ng-model="row.branch.tipodivisao" 
                        ng-selected="row.branch.tipodivisao"
                        ng-options="modo as modo.label for modo in row.branch.modoDivisao track by modo.valor">
                    </select>`,
                cellTemplateScope: {
                    abrirAplicarEmMassaModal: (branch) => {
                        this.abrirAplicarEmMassaModal(branch);
                    },
                    abrirAplicarEmMassaModalTotal: (branch) => {
                        this.abrirAplicarEmMassaModalTotal(branch);
                    },
                    escolhaDivisao: (branch, naoAlterouEscolha) => {
                        this.escolhaDivisao(branch, naoAlterouEscolha);
                    }
                }
            }
        ];
    }

    setActionColDef(){
        this.col_defs.push({
            actionColumn: true,
            field: null,
            cellTemplate: `
                <div class='table-btns-actions'
                ng-class="row.branch.actions.length==0 ? 'naopossuiacoes' : ''">
                    <nsj-actions>  
                        <nsj-action 
                            ng-repeat="action in row.branch.actions" 
                            ng-click='cellTemplateScope.funcao(row.branch)' 
                            icon='{{action.icon}}'
                            title='{{action.label}}'
                            ng-if="action.permission"
                        >
                        </nsj-action>
                    </nsj-actions>
                </div>
                `,
            cellTemplateScope: {
                // click: (acao: any) => {
                //     this.traduzMetodo.call(this, acao);
                // },
                funcao: (rowBranch: any) => {
                    this.abrirAplicarEmMassaModalParcial(rowBranch);
                },
                permissao: (action: any) => {
                    return true;
                    // if (action.permissionuser != null && action.permissionuser != undefined && action.permissionuser != '') {
                    //     return this.temPermissao(action.permissionuser);
                    // } else {
                    //     return true;
                    // }
                }
            }
        })
    }

    temInputEditavel(tipodivisao) {
        if (tipodivisao) {
            return (tipodivisao.valor === 'divisaoValor' || tipodivisao.valor === 'divisaoPercentual')
        }
    }

    compararResponsaveisfinanceiros(a, b) {
        const clienteA = (typeof a.responsavelfinanceiro == "object") ?
            a.responsavelfinanceiro.cliente : a.responsavelfinanceiro;
        const clienteB = (typeof b.responsavelfinanceiro == "object") ?
            b.responsavelfinanceiro.cliente : b.responsavelfinanceiro;

        let comparacao = 0;
        if (clienteA > clienteB) {
            comparacao = 1;
        } else if (clienteA < clienteB) {
            comparacao = -1;
        }
        return comparacao;
    }

    ordernarPorResponsaveisFinanceiros(respFinanceiros){
        return respFinanceiros.sort(this.compararResponsaveisfinanceiros)
    }

    getResponsaveisFinanceiros() {
        this.responsaveisFinanceiros = this.ordernarPorResponsaveisFinanceiros(this.entity.obj.responsaveisfinanceiros.slice());

        this.responsaveisFinanceiros.forEach((responsavelfinanceiro, index) => {
            const coluna = {
                field: 'responsavelFinanceiro',
                displayName: responsavelfinanceiro.responsavelfinanceiro.nomefantasia + '*',
                cellTemplate: `
                    <div class="container-flex">
                        <div class="custom-radio radio" ng-if="row.branch.level!=1 && row.branch.tipodivisao.valor === 'semDivisao'" >
                            <label>
                                <input 
                                type="radio" 
                                style="margin-right:6px"
                                name="responsavelfinanceiro{{row.branch._id_}}" 
                                ng-disabled=" row.branch.orcamentoValorreceber < 0"
                                ng-checked="row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada"
                                value="${responsavelfinanceiro.responsavelfinanceiro.cliente}" 
                                ng-click="cellTemplateScope.associaRespFinanceiroSemDivisao(row.branch,'${responsavelfinanceiro.responsavelfinanceiro.cliente}')"
                                >
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        
                        <nsj-checkbox 
                            ng-if="row.branch.level!=1 && row.branch.tipodivisao.valor !== 'semDivisao'" 
                            type="checkbox" 
                            style="margin-right:6px"
                            name="responsavelfinanceiro{{row.branch._id_}}" 
                            ng-disabled="row.branch.orcamentoValorreceber < 0"
                            ng-prop-checked="row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada" 
                            ng-on-ns_change="cellTemplateScope.manipularResponsabilidadeSelecionada(row.branch ,${index} ,$event)"
                            >
                        </nsj-checkbox> 

                        <span 
                            style="padding-left:6px"
                            ng-if="row.branch.level!=1 && row.branch.tipodivisao.valor === 'semDivisao' || row.branch.level!=1 && row.branch.tipodivisao.valor == 'divisaoIgual'" 
                            ng-class={'text-success':row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada}
                            ng-bind="(row.branch.obj.responsabilidadesfinanceiras[${index}].valorpagar | currency : 'R$' : 2) ">
                        </span>
                        
                        <input 
                        ng-if="row.branch.level!=1 && row.branch.tipodivisao.valor === 'divisaoValor'"
                        class="form-control" 
                        type="text"
                        ui-money-mask
                        max="row.branch.valorRestante"
                        ng-class={'text-success':row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada}
                        ng-disabled="row.branch.orcamentoValorreceber < 0 || row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada === false "
                        ng-model="row.branch.obj.responsabilidadesfinanceiras[${index}].valorpagar"
                        ng-change="cellTemplateScope.calcularTotalPagoNaBranchAtual(row.branch,${index})"
                        />

                        <input 
                        ng-if="row.branch.level!=1 && row.branch.tipodivisao.valor === 'divisaoPercentual' " 
                        class="form-control"
                        type="text"
                        ui-percentage-mask
                        ui-hide-group-sep
                        ng-class={'text-success':row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada}
                        ng-disabled=" row.branch.orcamentoValorreceber < 0 || row.branch.obj.responsabilidadesfinanceiras[${index}].selecionada === false"
                        ng-model="row.branch.obj.responsabilidadesfinanceiras[${index}].valorporcento"
                        ng-change="cellTemplateScope.calcularValorPorPorcentagem(row.branch , row.branch.obj.responsabilidadesfinanceiras[${index}].valorporcento,${index})"
                        />
                    </div>
                `,
                cellTemplateScope: {
                    manipularResponsabilidadeSelecionada: (responsabilidadefinanceira, index, { detail }) => {
                        this.manipularResponsabilidadeSelecionada(responsabilidadefinanceira, index, detail);
                    },

                    associaRespFinanceiroSemDivisao: (responsabilidadefinanceira, idResponsavelFinanceiro) => {
                        this.semDivisao(responsabilidadefinanceira, idResponsavelFinanceiro)
                    },

                    calcularTotalPagoNaBranchAtual: (branch, index) => {
                        this.calcularTotalPagoNaBranchAtual(branch, index);
                    },

                    calcularValorPorPorcentagem: (branch, valorporcento, index) => {
                        this.calcularValorPorPorcentagem(branch, valorporcento, index);
                    },
                },
            }

            this.col_defs.push(coluna);
        });
    }

    escolhaDivisao(branch, alterouEscolha) {
        switch (branch.tipodivisao.valor) {
            case 'semDivisao': {
                this.semDivisao(branch, alterouEscolha)
                break;
            }
            case 'divisaoIgual': {
                this.divisaoIgual(branch, alterouEscolha)
                break;
            }
            case 'divisaoPercentual': {
                this.divisaoPercentual(branch, alterouEscolha)
                break;
            }
            case 'divisaoValor': {
                this.divisaoValor(branch, alterouEscolha)
                break;
            }


        }
    }
    // Percorro as responsabilidades financeiras da linha selecionada e se o id for igual
    // ao item selecionado, atualizo seu valor com o valor do serviço e caso seja diferente zero o valor;
    // por fim atualizo o valor total do responsavel financeiro;

    semDivisao(branch, idResponsavelFinanceiro) {
        const valorServico = branch.orcamentoValor;
        branch.obj.responsabilidadesfinanceiras.forEach(responsabilidadefinanceira => {
            const { responsavelfinanceiro } = responsabilidadefinanceira
            const selecionada = responsavelfinanceiro === idResponsavelFinanceiro;
            if (selecionada) {
                responsabilidadefinanceira.selecionada = selecionada;
                responsabilidadefinanceira.valorpagar = valorServico;
            } else {
                responsabilidadefinanceira.valorpagar = 0;
                responsabilidadefinanceira.selecionada = false;
            }

            this.calcularValorTotalResponsaveisFinanceiros(responsavelfinanceiro);
        })

        const somatorio = this.somaValorBranch(branch);
        branch.totalPago = somatorio.totalPago
        branch.totalRestante = somatorio.totalRestante;
    }

    divisaoValor(branch, alterouEscolha = false) {
        branch.obj.responsabilidadesfinanceiras.forEach(responsabilidadefinanceira => {
            const { selecionada, responsavelfinanceiro } = responsabilidadefinanceira;

            if (alterouEscolha || (selecionada === false)) {
                responsabilidadefinanceira.valorpagar = 0;
            }

            responsabilidadefinanceira.selecionada = alterouEscolha || selecionada;

            this.calcularValorTotalResponsaveisFinanceiros(responsavelfinanceiro);
        })

        const somatorio = this.somaValorBranch(branch);
        branch.totalPago = somatorio.totalPago
        branch.totalRestante = somatorio.totalRestante;
    }

    divisaoPercentual(branch, alterouEscolha = false) {
        branch.obj.responsabilidadesfinanceiras.forEach(responsabilidadefinanceira => {
            const { selecionada, responsavelfinanceiro } = responsabilidadefinanceira;

            if (alterouEscolha || (selecionada === false)) {
                responsabilidadefinanceira.valorpagar = 0;
                responsabilidadefinanceira.valorporcento = 0;
            }

            responsabilidadefinanceira.selecionada = alterouEscolha || selecionada;

            this.calcularValorTotalResponsaveisFinanceiros(responsavelfinanceiro);
        })

        const somatorio = this.somaValorBranch(branch);
        branch.totalPago = somatorio.totalPago
        branch.totalRestante = somatorio.totalRestante;
    }

    divisaoIgual(branch, alterouEscolha = false) {
        const quantidadeResponsaveisFinanceiros = branch.obj.responsabilidadesfinanceiras.filter(resp => resp.selecionada === true).length;
        const valorTotalServico = branch.orcamentoValor;
        const valorPorResposavel = Number((valorTotalServico / quantidadeResponsaveisFinanceiros).toFixed(2));
        const valorResponsavelPrincipal = Number((valorTotalServico - (valorPorResposavel * (quantidadeResponsaveisFinanceiros - 1))).toFixed(2));

        branch.obj.responsabilidadesfinanceiras.forEach(responsabilidadefinanceira => {
            const {
                responsavelfinanceiro,
                selecionada,
                responsavelPrincipal
            } = responsabilidadefinanceira;

            if (selecionada && (alterouEscolha === false)) {
                responsabilidadefinanceira.valorpagar = responsavelPrincipal
                    ? valorResponsavelPrincipal
                    : valorPorResposavel;
            } else {
                responsabilidadefinanceira.selecionada = selecionada;
                responsabilidadefinanceira.valorpagar = selecionada ? valorResponsavelPrincipal : 0;
            }

            this.calcularValorTotalResponsaveisFinanceiros(responsavelfinanceiro);
        })
        const somatorio = this.somaValorBranch(branch);
        branch.totalPago = somatorio.totalPago
        branch.totalRestante = somatorio.totalRestante;
    }


    removerNo(id: string, hashIdentificador: string) {
        this.treetest = this.treetest.filter(dado => dado._id_ !== `${hashIdentificador}${id}`);
    }
    /**
     * @description Criação de novo nó e suas ações
     * @param identificador 
     * @param entity 
     * @param pai 
     * @param index 
     */
    obterNovoNode(tipo, entity, pai, index = null) {
        const icon = this.obterIcone();
        const identificador = this.obterIdEntity(tipo, entity);
        const actions = this.obterAcoesDoNode(tipo, entity, index);
        const noNovo = angular.copy(this.montarNoTree(tipo, entity, identificador, pai, '', actions, icon));
        return noNovo;
    }

    montarNoTree = function (tipo, entity, identificador, pai = null, hashAuxiliar = '', actions = [], iconLeaf) {
        const no = {
            _id_: identificador,
            _parentId_: pai,
            _info_: null,
            children: [],
            tipo: tipo,
            actions: actions,
            icons: { iconLeaf },
            tipodivisao: entity.tipodivisao || this.modoDivisao[0],
            modoDivisao: this.modoDivisao,
            responsabilidadesfinanceirasvalores: entity.responsabilidadesfinanceirasvalores,
            nome: entity.nome ? entity.nome : (entity.composicao ? entity.composicao.nome : entity.familia.descricao),
            orcamentoValor: entity.orcamentoValor >= 0 ? entity.orcamentoValor : null,
            orcamentoValorreceber: entity.orcamentoValorreceber >= 0 ? entity.orcamentoValorreceber : null,
            expanded: true,
            visible: true,
            selected: true,
            totalPago: entity.valorreceber,
            totalRestante: 0,
            responsavelFinanceiroAssociado: entity.responsavelFinanceiroAssociado ? entity.responsavelFinanceiroAssociado : null,
            obj: entity,
            orcamento: entity,
        }
        
        return no;
    }

    /**
     * Verificando se o identificador é de um capítulo, item, proposta item função ou proposta item familia
     * @param identificador 
     * @param entity 
     */
    obterIdEntity(identificador, entity) {
        switch (identificador) {
            case 'propostaitem':
                return entity.descricao;
            case 'propostaitemfuncao':
                return entity.orcamento;
            case 'propostaitemfamilia':
                return entity.orcamento;
        }
    }

    /**
     * Carregamento do ícone
     */
    obterIcone() {
        return 'fas fa-minus';
    }

    /**
     * @description Constrói as ações do node
     */
    obterAcoesDoNode(identificador, entity, index = null) {
        let acoes = [];
        switch (identificador) {
            case 'propostaitem':
                    acoes.push({
                        label: 'Aplicar divisão em massa',
                        permission: true,
                        icon: 'fas fa-divide',
                        size: 'xs',
                        color: 'primary',
                        funcao: (dado) => {
                            this.abrirAplicarEmMassaModalParcial(dado);
                        }
                    });
                break;
            default:
                acoes = [
                ];
                break;
        }
        return acoes;
    }


    manipularResponsabilidadeSelecionada(branch, index, detail) {
        const tipodivisao = branch.tipodivisao.valor;
        branch.obj.responsabilidadesfinanceiras[index].selecionada = detail.checked;
        this[tipodivisao](branch);
    }

    calcularTotalPagoNaBranchAtual(branch, index) {
        const somatorio = this.somaValorBranch(branch);
        if (parseInt(somatorio.totalRestante) < 0) {
            return;
        }
        branch.totalPago = somatorio.totalPago
        branch.totalRestante = somatorio.totalRestante;

        const idResponsavelFinanceiro = branch.obj.responsabilidadesfinanceiras[index].responsavelfinanceiro;
        this.calcularValorTotalResponsaveisFinanceiros(idResponsavelFinanceiro);
    }
    somaValorBranch(branch) {
        const totalPago = this.calcularTotal(branch.obj.responsabilidadesfinanceiras, 'valorpagar');
        const totalRestante = Number(branch.orcamentoValor - totalPago);
        
        return {
            totalPago: totalPago.toFixed(2),
            totalRestante: totalRestante.toFixed(2)
        }
    }
    calcularValorPorPorcentagem(branch, valorporcento, index) {
        const valorMaximo = branch.orcamentoValorreceber;
        const porcentagemTotalAtual = this.calcularTotal(branch.obj.responsabilidadesfinanceiras, 'valorporcento');
        branch.porcentagemMaxRestante = 1 - porcentagemTotalAtual;
        // Verifica se execedeu a porcentagem da linha;
        if (branch.porcentagemMaxRestante < 0) {
            return;
        }

        const valorpagar = (valorMaximo * valorporcento).toFixed(2);
        branch.obj.responsabilidadesfinanceiras[index].valorpagar = valorpagar;
        this.calcularTotalPagoNaBranchAtual(branch, index);
    }

    calcularTotal(arrayObjetos, propriedade) {
        return (
            arrayObjetos.reduce((acumulador: number, objetoAtual: any) => {
                if (objetoAtual[propriedade]) {
                    acumulador = acumulador + parseFloat(objetoAtual[propriedade]);
                }
                return acumulador;
            }, 0)
        )
    }

    calcularValorTotalResponsaveisFinanceiros(idResponsavelFinanceiro = null) {
        if (this.responsaveisFinanceirosAssociados.length === 0) {
            this.responsaveisFinanceirosAssociados = [
                { "id": null, "somatorio": null },
                { "id": null, "somatorio": null },
            ];
            this.responsaveisFinanceiros.forEach(responsavel => {
                let somatorio = 0;
                let obj = {
                    "id": responsavel.responsavelfinanceiro.cliente,
                    "somatorio": somatorio,
                }

                this.atualizarSomatorioFinanceiro(this.responsaveisFinanceirosAssociados, obj)
            });

        } else {
            const responsabilidadesfinanceiras = this.obterResponsabilidadesFinanceirasDaTree(this.treetest);

            const responsabilidadefinanceiraAtual = responsabilidadesfinanceiras.filter(el => el.responsavelfinanceiro === idResponsavelFinanceiro);
            const obj = {
                id: idResponsavelFinanceiro,
                somatorio: this.calcularTotal(responsabilidadefinanceiraAtual, 'valorpagar')
            };

            this.atualizarSomatorioFinanceiro(this.responsaveisFinanceirosAssociados, obj);
        }

        this.reloadScope();
    }

    obterResponsabilidadesFinanceirasDaTree(tree) {
        return tree
            .filter(element => element.tipo !== 'propostaitem')
            .reduce((acumumulador, element) => {
                const { responsabilidadesfinanceiras } = element.obj;
                return acumumulador = [...acumumulador, ...responsabilidadesfinanceiras];
            }, []);
    }

    atualizarSomatorioFinanceiro(arr, obj) {
        const index = arr.findIndex((e) => e.id === obj.id);
        if (index === -1) {
            arr.push(obj);
        } else {
            arr[index] = obj;
        }
    };

    /**
     * Organiza as informações buscadas na função 'carregarDadosTela' para funcionar no modelo de dados da tela
     */
    private organizarDadosTela() {
        let arrListaNodes: any[] = [];

        //mantem os orcamentos que tenha fornecedor e ele não espera pagamento, ou seja o estabelecimento.

        // Filtro propostas itens em que o fornecedor não espera pagamento da seguradora, ou possua estabelecimento
        this.listaOrcamentos = this.listaOrcamentos.filter((orcamento) => {
            
            //Se o orçamento estiver definido para não faturar, não processo ele e não exibo na tela de responsabilidade financeira
            if (orcamento.faturamentotipo == 1){
                return false;
            }

            // Busco fornecedor completo do array de fornecedores, referente ao proposta item, para verificar campo estabelecimentoid
            const fornecedor = this.arrFornecedores.find((fornecedorFind) => {
                return orcamento.fornecedor != null && orcamento.fornecedor.fornecedor == fornecedorFind.fornecedor;
            });
            return orcamento.fornecedor != null && ( 
                !orcamento.fornecedor.esperapagamentoseguradora || //não espera pagamento da seguradora
                (fornecedor != null && fornecedor.estabelecimentoid != null 
                    && fornecedor.estabelecimentoid.estabelecimento != null) //possui estabelecimento
            );
        });

        let totalServicos: number = 0;
        let totalServicosReceber: number = 0;
        let totalProdutos: number = 0;
        let totalProdutosReceber: number = 0;

        this.listaOrcamentos.forEach((orcamento) => {
            // Busco responsabilidaes financeiras referentes a essa propostaitem
            const responsabilidadesDoOrcamento = this.arrResponsabilidadesfinanceiras.filter((responsabilidadefinanceiraFilter) => {
                if(responsabilidadefinanceiraFilter.orcamento != null){
                    return responsabilidadefinanceiraFilter.orcamento == orcamento.orcamento;
                }
            });

            orcamento.responsabilidadefinanceira = responsabilidadesDoOrcamento[0];
            orcamento['orcamentoValor'] = orcamento.valor + orcamento.acrescimo - orcamento.desconto;// - orcamento.descontoglobal;
            orcamento['orcamentoValorreceber'] = orcamento.valorreceber;

            //SERVIÇO OU PRODUTO?
            if(orcamento.composicao !== null && orcamento.familia == null){
                //é serviço - Somo valores
                totalServicos += orcamento.orcamentoValor;
                totalServicosReceber += orcamento.orcamentoValorreceber;
            } else if (orcamento.familia !== null && orcamento.composicao == null){
                //é produto - Somo valores
                totalProdutos += orcamento.orcamentoValor;
                totalProdutosReceber += orcamento.orcamentoValorreceber;
            }

            // Adiciono a lista de nodes
            // Caso esteja definido pra faturar, adiciono node a lista
            if (orcamento.faturar) {
                arrListaNodes.push(orcamento);
            }
        });

        this.nodesOrcamentos = arrListaNodes;
        // NODES PAIS NOS QUAIS FICAM OS PRODUTOS E SERVIÇOS NA TREE
        let nodeComposicao = {
            "valor": 0,
            "acrescimo": 0,
            "desconto": 0,
            "familia": null,
            "descricaomanual": false,
            "descricao": "Serviços",
            "nome": "Serviços",
            "custo": "0.00",
            "motivo": null,
            "status": 2,
            "faturar": true,
            "valorreceber": 100,
            "tipoatualizacao": null,
            "full_count": 1,
        };
        let nodeProduto = {
            "valor": 0,
            "acrescimo": 0,
            "desconto": 0,
            "familia": null,
            "descricaomanual": false,
            "descricao": "Produtos",
            "nome": "Produtos",
            "custo": "0.00",
            "motivo": null,
            "status": 2,
            "faturar": true,
            "valorreceber": 100,
            "tipoatualizacao": null,
            "full_count": 1,
        };
        this.nodesOrcamentos.push(nodeComposicao);
        this.nodesOrcamentos.push(nodeProduto);

        this.treetest = this.nodesOrcamentos.map(item => {
            let pai = '';
            let tipo = '';
            if ((item.composicao || item.familia)) {
                pai = item.composicao ? 'Serviços' : 'Produtos';
                tipo = item.composicao ? 'propostaitemfuncao' : 'propostaitemfamilia';
                if(item.responsabilidadefinanceira != null) {
                    item.tipodivisao = this.modoDivisao.find((el) => {
                        return el.tipo === item.responsabilidadefinanceira.tipodivisao
                    });
                    item.responsabilidadesfinanceiras = this.obterDeResponsabilidadesAssociadas(item.responsabilidadefinanceira)
                    
                } else {
                    item.responsabilidadesfinanceiras = this.obterDeResponsaveisFinanceiros(this.responsaveisFinanceiros, item)
                }

            } else {
                pai = null;
                tipo = 'propostaitem';
            }

            const noNovo = this.obterNovoNode(tipo, item, pai);
            return noNovo;

        });

        this.treetest
            // .filter(element => element.tipo !== 'propostaitem')
            .filter(element => element.tipo !== 'propostaitem')
            .forEach(item => {
                item.obj.responsabilidadesfinanceiras.forEach(({ selecionada, responsavelfinanceiro }) => {
                    if (selecionada) {
                        this.calcularValorTotalResponsaveisFinanceiros(responsavelfinanceiro)
                    }
                })
            });

        this.reloadScope();
        this.busyContratoMaisPaganteModal = false;
    }

    temResponsabilidadesAssociadas(idPai) {
        return this.responsabilidadesAssociadas[idPai]?.length > 0;
    }

    naoTemNovosResponsaveisFinanceiros(pai){
        return this.responsabilidadesAssociadas[pai][0].responsabilidadesfinanceirasvalores.length === this.responsaveisFinanceiros.length
    }

    obterDeResponsabilidadesAssociadas(responsabilidadefinanceira) {
        const responsabilidadesValores = responsabilidadefinanceira.responsabilidadesfinanceirasvalores.slice();

        responsabilidadesValores.forEach(responsabilidadeValor => {
            const responsavelfinanceiro = this.responsaveisFinanceiros
                .find(resFin => resFin.responsavelfinanceiro.cliente === responsabilidadeValor.responsavelfinanceiro);
            responsabilidadeValor.responsavelPrincipal = responsavelfinanceiro.principal;
            responsabilidadeValor.selecionada = (responsabilidadeValor.valorpagar > 0);
            responsabilidadeValor.valorporcento = (responsabilidadeValor.valorpagar / responsabilidadefinanceira.valorservico);
        })
        return this.ordernarPorResponsaveisFinanceiros(responsabilidadesValores);
    }

    obterDeResponsaveisFinanceiros(responsaveisfinanceiros, itemServico) {
       const responsabilidadesfinanceiras =  responsaveisfinanceiros.map(responsavel => {
            return ({
                razaosocial: responsavel.responsavelfinanceiro.razaosocial,
                responsavelfinanceiro: responsavel.responsavelfinanceiro.cliente,
                valorpagar: responsavel.principal ? itemServico.orcamentoValor : 0,
                selecionada: responsavel.principal,
                responsavelPrincipal: responsavel.principal
            });
        })

        return this.ordernarPorResponsaveisFinanceiros(responsabilidadesfinanceiras);
    }

    abrirAplicarEmMassaModalParcial(branch) {
        this.CrmContratoPaganteAcoesModalService.open({
            responsaveisFinanceiros: this.responsaveisFinanceiros,
            modoDivisao: this.modoDivisao,
            nomeExibicaoItem: branch._id_
        })
        .result.then((resp) => {
            const semDivisao = resp.tipodivisao.valor === 'semDivisao';
            const idResponsavelFinanceiro = resp.selecionados[0];
            const parametroDefault = semDivisao ? idResponsavelFinanceiro : false;

            branch.children.forEach(child => {
                child.tipodivisao = angular.copy(resp.tipodivisao);
                child.obj.responsabilidadesfinanceiras.forEach(el => {
                    el.selecionada = resp.selecionados.includes(el.responsavelfinanceiro)

                    if (this.temInputEditavel(child.tipodivisao)) {
                        el.valorpagar = 0;
                        el.valorporcento = 0;
                    }
                })

                this.escolhaDivisao(child, parametroDefault);
            })
        })
    }

    abrirAplicarEmMassaModalTotal(branch) {
        this.CrmContratoPaganteAcoesModalService.open({
            responsaveisFinanceiros: this.responsaveisFinanceiros,
            modoDivisao: this.modoDivisao,
            nomeExibicaoItem: 'todos os itens'
        })
        .result.then((resp) => {
            const semDivisao = resp.tipodivisao.valor === 'semDivisao';
            const idResponsavelFinanceiro = resp.selecionados[0];
            const parametroDefault = semDivisao ? idResponsavelFinanceiro : false;

            this.treetest.forEach(treeItem => {
                treeItem.children.forEach(child => {
                    child.tipodivisao = angular.copy(resp.tipodivisao);
                    child.obj.responsabilidadesfinanceiras.forEach(el => {
                        el.selecionada = resp.selecionados.includes(el.responsavelfinanceiro)
    
                        if (this.temInputEditavel(child.tipodivisao)) {
                            el.valorpagar = 0;
                            el.valorporcento = 0;
                        }
                    })
    
                    this.escolhaDivisao(child, parametroDefault);
                })
            });
        })
    }

    abrirAplicarEmMassaModal(branch) {
        this.CrmContratoPaganteAcoesModalService.open({
            responsaveisFinanceiros: this.responsaveisFinanceiros,
            modoDivisao: this.modoDivisao,
        })
        .result.then((resp) => {
            const semDivisao = resp.tipodivisao.valor === 'semDivisao';
            const idResponsavelFinanceiro = resp.selecionados[0];
            const parametroDefault = semDivisao ? idResponsavelFinanceiro : false;

            branch.children.forEach(child => {
                child.tipodivisao = angular.copy(resp.tipodivisao);
                child.obj.responsabilidadesfinanceiras.forEach(el => {
                    el.selecionada = resp.selecionados.includes(el.responsavelfinanceiro)

                    if (this.temInputEditavel(child.tipodivisao)) {
                        el.valorpagar = 0;
                        el.valorporcento = 0;
                    }
                })

                this.escolhaDivisao(child, parametroDefault);
            })
        })

    }
    reloadScope() {
        this.$scope.$applyAsync();
        const treeFormated = this.tree.getArvore(this.treetest);
        this.lstPropostasItens = treeFormated;
    }

    traduzMetodo(metodo) {
        return eval(metodo);
    };

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

type treeExpand = {
    field: string;
    displayName: string;
    sortable: boolean;
    filterable: boolean;
    cellTemplate?: string;
};

type treeColDefs = ({ field: string; displayName: string; cellTemplate?: undefined; cellTemplateScope?: undefined; } | { field: any; cellTemplate: string; cellTemplateScope: {}; displayName?: undefined; } | { field: string; displayName: string; cellTemplate: string; cellTemplateScope?: undefined; })[];


export class CrmContratoMaisPaganteModalService {
    static $inject = ['CrmPropostascapitulos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }

    /* Incluindo o pai como parametro opcional*/
    open(parameters: any, ) {
        return this.$uibModal.open({
            template: require('../../Crm/Atcs/contrato-mais-pagante-modal.html'),
            controller: 'CrmContratoMaisPaganteModalController',
            controllerAs: 'crm_ctrt_ms_pgt_cntrllr',
            windowClass: 'modal-lg-wrapper',
            resolve: {
                entity: async () => {
                    let entity = parameters;
                    return entity;
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }

}

// Interfaces
interface IResponsabilidadefinanceira {
    responsabilidadefinanceira: string;
    responsabilidadesfinanceirasvalores: IResponsabilidadefinanceiravalor[];
    propostaitem: string;
    propostaitemfamilia: string;
    propostaitemfuncao: string;
    orcamento: string;
}
interface IResponsabilidadefinanceiravalor {
    responsavelfinanceiro: string;
    valorpagar: number;
    contrato: string;
}
interface IPropostaItem extends DadosNodeOrcamento {
    propostaitem?: string;
    nome?: string;
    possuifamilias?: boolean;
    possuifuncoes?: boolean;
    servicoorcamento?: IOrcamento;
    fornecedor: IFornecedor;

    // Dados utilizados na tela
    propostasitensfamilias?: IPropostasItemFamilia[];
    propostasitensfuncoes?: IPropostasItemFuncao[];
}
interface IPropostasItemFamilia extends DadosNodeOrcamento {
    propostaitemfamilia?: string;
    propostaitem?: string;
    valor?: number;
    quantidade?: number;
    familia?: IFamilia;

    // Dados utilizados na tela
    responsabilidadefinanceira?: string;
}
interface IFamilia {
    descricao: string;
}
interface IPropostasItemFuncao extends DadosNodeOrcamento {
    propostaitemfuncao?: string;
    propostaitem?: string;
    quantidade?: number;
    funcao?: IFuncao;

    // Dados utilizados na tela
    responsabilidadefinanceira?: string;
}
interface IFuncao {
    descricao: string;
}
interface IOrcamento {
    orcamento?: string;
    propostaitem?: IPropostaItem;
    propostaitemfamilia?: IPropostasItemFamilia;
    propostaitemfuncao?: IPropostasItemFuncao;
    valor: number;
    execucaoservico: boolean;
    execucaodeservico: boolean;
    faturar?: boolean;
    faturamentotipo: number;
    valorreceber?: number;
    desconto?: number;
    descontoglobal?: number;
    acrescimo?: number;
    status?: number;
    fornecedor?: IFornecedor;
    composicao?: string;
    familia?: string;
    responsabilidadefinanceira: IResponsabilidadefinanceira;

    orcamentoValor?: number;
    orcamentoValorreceber?: number;
}
interface IFornecedor{
    fornecedor: string;
    estabelecimentoid?: IEstabelecimento;
    esperapagamentoseguradora?: boolean;
}
interface IEstabelecimento{
    estabelecimento: string;
}
interface DadosNodeOrcamento {
    orcamentoValor?: number;
    orcamentoValorreceber?: number;
    orcamentoStatus?: number;
    faturar?: boolean;
}