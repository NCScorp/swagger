

import angular = require('angular');
import { Tree } from '../../Commons/tree';
import { montarNoTree, editarNode } from '../../Commons/utils';
import  moment from 'moment';

import { CrmTemplatespropostasCreatePropostaFormService } from './../../Crm/Templatespropostas/templateproposta-modal';
import { CrmPropostasitensPretadoresModalService } from '../Propostasitens/modal-prestadores';

import { CrmAtcsFormHistoricoService } from './../../Crm/Atcshistorico/modal';
import { ISecaoController } from './classes/isecaocontroller';

export class CrmAtcsPedidosController {

    static $inject = [
        '$scope',
        'toaster',
        'entity',
        'CrmAtcs',
        'utilService',
        '$http',
        'nsjRouting',
        '$uibModal',
        'CrmPropostasFormService',
        'CrmPropostasFormShowService',
        'CrmPropostascapitulosFormService',
        'CrmPropostascapitulos',
        'CrmPropostas',
        'CrmPropostasitensFormService',
        'CrmPropostasitens',
        'CrmPropostasitensfamiliasFormService',
        'CrmPropostasitensfamilias',
        'CrmPropostasitensfuncoesFormService',
        'CrmPropostasitensfuncoes',
        'Tree',
        "$q",
        'CrmTemplatespropostas',
        'CrmTemplatespropostasCreatePropostaFormService',
        'debounce',
        '$rootScope',
        '$window',
        'Usuarios',
        'CrmOrcamentosObservacoesSubModalService',
        'CrmPropostasitensPretadoresModalService',
        'CrmAtcsFormHistoricoService',
        'secaoctrl'
    ];
    valorDigitado: string;
    private _capitulo: any;
    private _propostaitem: any;
    private _proposta: any;
    private _index: number;
    private _propostaitemfuncao: any;
    private _propostaitemfamilia: any;
    private _atc: any;
    public expanding_property: treeExpand;
    public col_defs: any;
    public proposta: any = {};
    private idCount: any = 0;
    public busy: boolean = true;
    public treetest = [];
    public propostacapitulos: any = {};
    public propostaitemfuncao: any = {};
    public busyPedidos: boolean = false;
    public busyInicializacao: boolean = false;
    public fornecedor: any = 0;
    public propostasitensqtde = 0;
    public loading_deferred: any = null;
    public inicializado: boolean = false;

    public linhaFornecedorCLicado: any = null;
    public mostrarToaster = false;
    public editado: { id: any; fornecedor: any; };

    public linksExternos: any;
    public treePedidos: TreeControl = {};
    protected _nodeSelecionadoErro: Map<string,any> = new Map();

    private arrPropostas: IProposta[] = [];
    private arrPropostascapitulos: IPropostaCapitulo[] = [];
    private arrPropostasitens: IPropostaItem[] = [];
    private arrPropostasitensfamilias: IPropostasItemFamilia[] = [];
    private arrPropostasitensfuncoes: IPropostasItemFuncao[] = [];
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    /**
     * Faz o controle se o accordion de pedidos já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    constructor(
        public $scope: any,
        public toaster: any,
        public entity: any,
        public entityService: any,
        public utilService: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public $uibModal: any,
        public CrmPropostasFormService: any,
        public CrmPropostasFormShowService: any,
        public CrmPropostascapitulosFormService: any,
        public CrmPropostascapitulos: any,
        public CrmPropostas: any,
        public CrmPropostasitensFormService: any,
        public CrmPropostasitens: any,
        public CrmPropostasitensfamiliasFormService: any,
        public CrmPropostasitensfamilias: any,
        public CrmPropostasitensfuncoesFormService: any,
        public CrmPropostasitensfuncoes: any,
        public tree: Tree,
        public $q: angular.IQService,
        public CrmTemplatespropostas: any,
        public CrmTemplatespropostasCreatePropostaFormService: CrmTemplatespropostasCreatePropostaFormService,
        public debounce: any,
        public $rootScope: angular.IRootScopeService,
        public $window: angular.IWindowService,
        public Usuarios: any,
        public CrmOrcamentosObservacoesSubModalService: any,
        public CrmPropostasitensPretadoresModalService: CrmPropostasitensPretadoresModalService,
        public CrmAtcsFormHistoricoService : CrmAtcsFormHistoricoService,
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
                this.busyInicializacao = true;
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                // Chamo função que carrega dados da tela;
                this.carregarDadosTela();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }

        //Dado uma propostaitem, busca na tree seu nó e limpa o lookup de fornecedor
        this.$scope.$on('desvinculaFornecedorTreePedidos', (event, data) => {
            
            if (data != undefined && data != null){
                var idPropostaItem = data.propostaitem;

                //Percorrendo a tree para encontrar o nó e limpar lookup
                for(let i = 0; i < this.treetest.length; i++){
    
                    if(this.treetest[i].tipo === 'item' && this.treetest[i].obj.propostaitem === idPropostaItem){
                        this.treetest[i].obj.fornecedor = null;
                        this.reloadScope();
                        break;
                    }
    
                }
            }
        });

        //Se o atendimento for cancelado ou finalizado, recarrego card de pedidos e ficha financeira
        this.$scope.$on('cancelouFinalizouAtc', (event, data) => {
            
            this.carregarDadosTela();
            
        });

    }

    /* Carregamento */
    Init() {
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;
            // this.busyPedidos = true;
            this.linksExternos = this.$window.nsj.globals.a.links;

            // Inicializo funções que ficam escutando eventos
            this.initColDef();
            this.onAddPedido();
            // Capítulo
            this.ouvirSalvarCapitulo();
            this.ouvirRemoverCapitulo();
            // Propostaitem
            this.ouvirSalvarPropostaitem();
            this.ouvirSalvarPropostaitemError();
            this.ouvirRemoverPropostaitem();
            this.ouvirRemoverPropostaitemError();
            // Propostaitemfamilia
            this.ouvirSalvarPropostaitemfamilia();
            this.ouvirSalvarPropostaitemfamiliaError();
            this.ouvirRemoverPropostaitemfamilia();
            this.ouvirRemoverPropostaitemfamiliaError();
            // Propostaitemfunção
            this.ouvirSalvarPropostaitemfuncao();
            this.ouvirSalvarPropostaitemfuncaoError();
            this.ouvirRemoverPropostaitemfuncao();
            this.ouvirRemoverPropostaitemfuncaoError();
            //Ouvir orçamento aceito
            this.ouvirOrcamentoAceito();

            //atc é necessario para gerar item contrato eventualmente
            this._atc = this.entity.negocio;
        }
        
        // Faz as requisições de carregamento da tela
        // this.carregarDadosTela();
    }

    /**
     * Cada vez que um orçamento for aceito, seto a propriedade orcamento aprovado do fornecedor no array de propostas itens para true
     */
    private ouvirOrcamentoAceito(){

        this.$scope.$on('crm_orcamentos_aprovar', (response, entity) => {

            //Pegando a posição da proposta item no array de propostas itens
            this.arrPropostasitens.forEach(function (propostaItemIndex) {
                if (propostaItemIndex.fornecedor && propostaItemIndex.fornecedor.fornecedor == entity.fornecedor.fornecedor){
                    propostaItemIndex.fornecedor.orcamentoaprovado = true;
                };
            });
        });

    }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela(config: {
        propostaitem: IPropostaItem
    } = null){

        this.busyPedidos = true;

        if (config == null) {
            // Busco propostas do negócio
            this.arrPropostas = this.entity.propostas;
            this.proposta = this.arrPropostas[0];

            // Busco Propostas capitulos e propostasitens filtrando por proposta
            [
                this.arrPropostascapitulos,
                this.arrPropostasitens
            ] = await Promise.all([
                this.getPropostascapitulosFromApi(this.arrPropostas[0].proposta),
                this.getPropostasitensFromApi(this.entity.negocio, this.arrPropostas[0].proposta)
            ]);

            // Atualizo dados na tela, para aparecer a listagem dos capítulos
            this.organizarDadosTela();

            //Uma vez que o accordion foi carregado, seto a variável de controle para true
            this.accordionCarregado = true;

            this.reloadScope();
        }

        let dados = await this.getPropostasitensfamiliasFuncoesUnificado(this.entity.negocio, this.arrPropostas[0].proposta);

        this.arrPropostasitensfamilias = dados.familias;
        this.arrPropostasitensfuncoes = dados.funcoes;

        // // Busco Propostas itens familias e propostas itens funcoes, filtrando por proposta item
        // let dadosFuncoesFamilia: {
        //     arrPromises: Promise<any>[],
        //     arrTipoInfo: ('familia' | 'funcao')[],
        //     arrRetornoApi: any[]
        // } = {
        //     arrPromises: [], // Promisses de busca por família e função
        //     arrTipoInfo: [], // Se é família ou função
        //     arrRetornoApi: [] // Dados retornados das requisições
        // };
        
        // // Caso possua configuração para carregar somente os dados de uma propostaitem, busco somente os filhos dessa propostaitem.
        // this.arrPropostasitens.filter((propostaitem) => {
        //     return (config == null) || (config != null && config.propostaitem.propostaitem == propostaitem.propostaitem); 
        // }).forEach((propostaitem) => {
        //     // Adiciono promise de propostaitemfamilia
        //     if (propostaitem.possuifamilias || config != null) {
        //         dadosFuncoesFamilia.arrPromises.push(this.getPropostasitensfamiliasFromApi(propostaitem.propostaitem));
        //         dadosFuncoesFamilia.arrTipoInfo.push('familia');
        //         dadosFuncoesFamilia.arrRetornoApi.push([]);
        //     }

        //     if (propostaitem.possuifuncoes || config != null) {
        //         // Adiciono promise de propostaitemfuncao
        //         dadosFuncoesFamilia.arrPromises.push(this.getPropostasitensfuncoesFromApi(propostaitem.propostaitem));
        //         dadosFuncoesFamilia.arrTipoInfo.push('funcao');
        //         dadosFuncoesFamilia.arrRetornoApi.push([]);
        //     }
        // });

        // // Se passou a configuração pra buscar propostaitem, removo as funções e famílias filhas da lista, pois serão atualizadas.
        // if (config != null && config.propostaitem.propostaitem) {
        //   // Removo as famílias filhas do propostas item.
        //   this.arrPropostasitensfamilias = this.arrPropostasitensfamilias.filter((propostaitemFamilia) => {
        //     return propostaitemFamilia.propostaitem != config.propostaitem.propostaitem;
        //   });

        //   // Removo as funções filhas do propostas item.
        //   this.arrPropostasitensfuncoes = this.arrPropostasitensfuncoes.filter((propostaitemFuncao) => {
        //     return propostaitemFuncao.propostaitem != config.propostaitem.propostaitem;
        //   });
        // }

        // // Faço todas as requisições de propostasitensfamilias e propostasitensfuncoes ao mesmo tempo, para otimizar
        // dadosFuncoesFamilia.arrRetornoApi = await Promise.all(dadosFuncoesFamilia.arrPromises);
    
        // if (config == null) {
        //     this.arrPropostasitensfamilias = [];
        //     this.arrPropostasitensfuncoes = [];
        // }

        // // Distribuo as informações para as variaveis de propostasfamilias e propostasfuncoes
        // dadosFuncoesFamilia.arrTipoInfo.forEach((tipo, index) => {
        //     if (tipo == "familia") {
        //         this.arrPropostasitensfamilias = this.arrPropostasitensfamilias.concat(dadosFuncoesFamilia.arrRetornoApi[index]);
        //     } else {
        //         this.arrPropostasitensfuncoes = this.arrPropostasitensfuncoes.concat(dadosFuncoesFamilia.arrRetornoApi[index]);
        //     }
        // });

        const idPaiExpandir = (config == null) ? null : 'capitulo' + config.propostaitem.propostacapitulo.propostacapitulo;
        this.organizarDadosTela(idPaiExpandir);

        // Desativo loading
        this.busyPedidos = false;
        this.busyInicializacao = false;
        this.reloadScope();
        this.atualizaFornecedorCapitulo();
    }

    /**
     * Realiza o calculo dos valores dos itens na Tree
     */
    private calcularValoresTree(){
        this.proposta.valor = 0;

        // Percorro capítulos da proposta para somar o valor da proposta
        this.arrPropostascapitulos.filter((capitulo) => {
            return capitulo.proposta == this.proposta.proposta;
        }).forEach((capitulo) => {
            capitulo.valor = 0;

            // Percorro propostasitens do capitulo para somar o valor do capítulo
            this.arrPropostasitens.filter((propostaitem) => {
                return propostaitem.propostacapitulo.propostacapitulo == capitulo.propostacapitulo;
            }).forEach((propostaitem) => {
                propostaitem.valor = 0;

                // Percorro propostasitensfamilias da propostaitem para somar o valor da propostaitem
                this.arrPropostasitensfamilias.filter((propostaitemfamilia) => {
                    return propostaitemfamilia.propostaitem == propostaitem.propostaitem;
                }).forEach((propostaitemfamilia) => {
                    propostaitem.valor += propostaitemfamilia.valor * propostaitemfamilia.quantidade;
                });

                // Atualizo valor do capítulo.
                capitulo.valor += propostaitem.valor;
            });

            // Atualizo valor da proposta.
            this.proposta.valor += capitulo.valor;
        });
    }

    /**
     * Organiza as informações buscadas na função 'carregarDadosTela' para funcionar no modelo de dados da TreeGrid
     * @param itemExpandir 
     */
    private organizarDadosTela(itemExpandir: string = null){
        // Crio um array com o id dos itens que estão expandidos atualmente
        const arrItensExpandidos = this.treetest
            .filter( item => item.expanded)
            .map( item => item._id_);
        
        // Se for passado um item específico para expandir, adiciono ele ao array com itens expandidos
        if (itemExpandir != null) {
            arrItensExpandidos.push(itemExpandir);
        }

        // Limpo lista de itens, pois vão ser adicionados novamente
        this.treetest = [];
        const propostaIndex = 0;
        this.organizarDadosTreePropostasCapitulos(propostaIndex);

        this.atualizarCoberturaApolicePropostaItem();
        this.calcularValoresTree();

        // Expando itens que já estavam expandidos antes da reorganização
        this.treetest.forEach((item) => {
            item.expanded = arrItensExpandidos.indexOf(item._id_) > -1;
        });
    }

    /**
     * Organizo os dados referentes aos capítulos na Tree
     * @param propostaIndex 
     */
    private organizarDadosTreePropostasCapitulos(propostaIndex: number){
        this.arrPropostascapitulos.forEach((capitulo) => {
            // Crio novo nó referente ao capítulo
            const pai = capitulo.pai == null ? null : 'capitulo' + capitulo.pai;
            const adicionarNovoNo = false;

            // Carrego os nós filhos
            this.organizarDadosTreePropostaitem(capitulo, propostaIndex);

            const noNovo = this.obterNovoNode('capitulo', capitulo, pai, adicionarNovoNo, propostaIndex);

            // Adiciono nó de capítulo à TreeList
            this.treetest.push(noNovo);
        });

        // Percorro os itens da tree que são capítulos, e não possuem pai para atualizar o vínculo do capítulo na tela.
        this.treetest.filter((node) => {
            return node.tipo === 'capitulo' && node._parentId_ == null
        }).forEach((capitulo) => {
            return this.atualizarVinculoCapituloNaTela(capitulo)
        });
    };

    /**
     * Organizo os dados referentes aos propostasitens na Tree
     * @param capituloEntity 
     * @param indexProposta 
     */
    private organizarDadosTreePropostaitem(capituloEntity: IPropostaCapitulo, indexProposta: number){
        // Busco os propostasitens filtrando pelo capítulo
        const arrPropostasitens = this.arrPropostasitens.filter((propostaitem) => {
            return propostaitem.propostacapitulo.propostacapitulo == capituloEntity.propostacapitulo
        });
        this.propostasitensqtde += arrPropostasitens.length;

        let temMesmoFornecedor: boolean = true;
        let fornecedor = null;

        arrPropostasitens.forEach((propostaitem, index) => {
            if (index == 0) {
                fornecedor = propostaitem.fornecedor;
            }

            // Verificos se tem fornecedores diferentes
            if (temMesmoFornecedor && (
                    (fornecedor && propostaitem.fornecedor && fornecedor.fornecedor != propostaitem.fornecedor.fornecedor) ||
                    (fornecedor != propostaitem.fornecedor)
                )) {
                temMesmoFornecedor = false;
            }

            // Carrego os nós filhos
            this.organizarDadosTreePropostaitemfamilia(propostaitem);
            this.organizarDadosTreePropostaitemfuncao(propostaitem);

            const paiCapitulo = 'capitulo' + capituloEntity.propostacapitulo;
            const adicionarNovoNo = false;
            propostaitem.proposta = capituloEntity.proposta;
            propostaitem = this.calculodePrazo(propostaitem);

            // Crio nó referente a proposta item
            const noNovo = this.obterNovoNode('item', propostaitem, paiCapitulo, adicionarNovoNo, indexProposta);

            // Adiciono nó de propostaitem à TreeList
            this.treetest.push(noNovo);
        });

        // Se todos os filhos tem o mesmo fornecedor, seto fornecedor do pai
        if (temMesmoFornecedor) {
            capituloEntity.fornecedor = fornecedor;
        }
    };

    /**
     * Organizo os dados referentes aos propostasitensfamilias na Tree
     * @param propostaitemEntity 
     */
    private organizarDadosTreePropostaitemfamilia(propostaitemEntity: IPropostaItem){
        // Busco propostasitensfamilias referentes ao propostaitem
        const arrPropostasitensfamilias = this.arrPropostasitensfamilias.filter((propostaitemfamilia) => {
            return propostaitemfamilia.propostaitem == propostaitemEntity.propostaitem;
        });

        arrPropostasitensfamilias.forEach((propostaitemfamilia) => {
            const paiItem = 'item' + propostaitemEntity.propostaitem;
            const adicionarNovoNo = false;
            
            // Crio objeto utilizado para criacao do Nó
            let itemFamilias: any = angular.copy(propostaitemfamilia);
            itemFamilias.descricao = itemFamilias.familia_descricao;
            itemFamilias.tarefapai = angular.copy(propostaitemEntity.tarefa);
            itemFamilias = { ...itemFamilias, ...itemFamilias.familia }
            itemFamilias.proposta = propostaitemEntity.proposta;

            //concatenando a quantidade ao nome da familia
            if (itemFamilias.nome || itemFamilias.descricao) {
                if (itemFamilias.nome) {
                    itemFamilias.nome = itemFamilias.quantidade + 'x ' + itemFamilias.nome;
                }
                else {
                    itemFamilias.descricao = itemFamilias.quantidade + 'x ' + itemFamilias.descricao;
                }
            }

            // Crio nó referente a proposta item familia
            const no = this.obterNovoNode('itemfamilias', itemFamilias, paiItem, adicionarNovoNo);

            // Adiciono nó de propostaitemfamilia à TreeList
            this.treetest.push(no);
        });
    };

    /**
     * Organizo os dados referentes aos propostasitensfunções na Tree
     * @param propostaitemEntity 
     */
    private organizarDadosTreePropostaitemfuncao(propostaitemEntity: IPropostaItem){
        // Busco propostasitensfunções referentes ao propostaitem
        const arrPropostasitensfuncoes = this.arrPropostasitensfuncoes.filter((propostaitemfuncao) => {
            return propostaitemfuncao.propostaitem == propostaitemEntity.propostaitem;
        });

        arrPropostasitensfuncoes.forEach((propostaitemfuncao) => {
            const paiItem = 'item' + propostaitemEntity.propostaitem;
            const adicionarNovoNo = false;
            
            // Crio objeto utilizado para criacao do Nó
            let itemFuncoes: any = angular.copy(propostaitemfuncao);
            itemFuncoes.descricao = itemFuncoes.funcao_descricao;
            itemFuncoes.tarefapai = angular.copy(propostaitemEntity.tarefa);
            itemFuncoes = { ...itemFuncoes, ...itemFuncoes.funcao }
            itemFuncoes.proposta = propostaitemEntity.proposta;

            //concatenando a quantidade ao nome da funcao
            if (itemFuncoes.nome || itemFuncoes.descricao) {
                if (itemFuncoes.nome) {
                    itemFuncoes.nome = itemFuncoes.quantidade + 'x ' + itemFuncoes.nome;
                }
                else {
                    itemFuncoes.descricao = itemFuncoes.quantidade + 'x ' + itemFuncoes.descricao;
                }
            }

            // Crio nó referente a proposta item função
            const no = this.obterNovoNode('itemfuncoes', itemFuncoes, paiItem, adicionarNovoNo);

            // Adiciono nó de propostaitemfunção à TreeList
            this.treetest.push(no);
        });
    };

    /**
     * Busca propostas capítulos da API
     * @param proposta 
     */
    private getPropostascapitulosFromApi(proposta: string): Promise<IPropostaCapitulo[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostascapitulos_index', { 'proposta': proposta }, true, true)
            }).then((dados: any) => {
                resolve(dados.data);
            }).catch((error) => {
                reject(error);
            })
        });
    }

    /**
     * Busca propostas itens da API
     * @param atc 
     * @param proposta 
     */
    private getPropostasitensFromApi(atc: string, proposta: string): Promise<IPropostaItem[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_index', { negocio: atc, proposta }, true, true)
            }).then((response: any) => {
                response.data.forEach(propostaItem => {
                    propostaItem.negocio = {negocio: atc}
                });
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Busca todas as funcoes e familias da proposta
     */
     private getPropostasitensfamiliasFuncoesUnificado(atc, proposta) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_get_familias_funcoes_proposta', { 'negocio': atc, 'proposta': proposta}, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Busca propostas itens famílias da API
     * @param propostaitem 
     */
    private getPropostasitensfamiliasFromApi(propostaitem: string): Promise<IPropostasItemFamilia[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_familias_index', { propostaitem }, true, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Busca propostas itens funções da API
     * @param propostaitem 
     */
    private getPropostasitensfuncoesFromApi(propostaitem: string): Promise<IPropostasItemFuncao[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_funcoes_index', { propostaitem }, true, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    treeAddLinha(paiId = null) {
        let keepGoing = true;
        this.treetest.forEach(element => {
            if (element.tipo === 'treeaddlinha') {
                keepGoing = false;
            }
        });

        if (keepGoing) {
            let obj = {
                "full_count": 6,
                "nome": "",
                "possuifilho": false,
                "proposta": "treeaddlinha",
                "propostacapitulo": "treeaddlinha",
                "pai": paiId,
            }

            let pai = paiId == null ? null : 'capitulo' + paiId;

            this.expandirPai(paiId);

            let adicionarNovoNo = true;
            let linhaNova = this.obterNovoNode('treeaddlinha', obj, pai, adicionarNovoNo);
            this.treetest.push(linhaNova);
            this.reloadScope();
        }
    }

    removerAddLinha() {
        this.removerNo('treeaddlinha', 'treeaddlinha');
        let index = this.treetest.findIndex(obj => obj.tipo === "treeaddlinha");
        if (index != -1) {
            this.treetest.splice(index, 1);
        }
        this.reloadScope();
    }

    expandirPai(paiId: string) {
        this.treetest.forEach(element => {
            if (element.obj.propostacapitulo == paiId || element.obj.propostaitem == paiId) {
                element.expanded = true;
            }
        });
    }


    /* tree */

    /* --- */
    initColDef() {
        this.expanding_property = {
            field: ' ',
            displayName: ' ',
            sortable: true,
            filterable: true,


        }

        /* carrega as colunas */
        this.col_defs = [
            {
                field: 'nome',
                displayName: 'Serviços',
                cellTemplate: `
                <div class="add-tree-table-form container-flex">

                    <!-- Se o nó não for do tipo capítulo e nem do tipo de uma linha nova, então só mostro o seu nome, sem ser possível editar -->
                    <div ng-if="row.branch.tipo != 'capitulo' && row.branch.tipo != 'treeaddlinha'">
                        <span ng-bind="row.branch.nome"></span>
                    </div>

                    <input ng-if="row.branch.tipo === 'treeaddlinha' && row.branch.obj.adicionarNovo==true" ng-class="{novoinput: row.branch.obj.adicionarNovo==true}" class="adiciona-pendencia-nova-input" ng-model="row.branch.nome" ng-model-options="{ getterSetter: true }" ns-enter="cellTemplateScope.add({'texto':row.branch.nome},row.branch.obj.pai)">

                    <input ng-if="row.branch.tipo === 'capitulo' && row.branch.obj.adicionarNovo==false && !cellTemplateScope.verificaAtcFinalizado()" ng-class="{novoinput: row.branch.obj.adicionarNovo==true}" class="adiciona-pendencia-nova-input" ng-model="row.branch.nome"  ng-model-options="{ getterSetter: true }" ns-enter="cellTemplateScope.edit({'texto':row.branch.nome},row.branch.obj)">
                    <input ng-if="row.branch.tipo === 'capitulo' && row.branch.obj.adicionarNovo==true && !cellTemplateScope.verificaAtcFinalizado()" ng-class="{novoinput: row.branch.obj.adicionarNovo==true}" class="adiciona-pendencia-nova-input" ng-model="row.branch.nome" ng-model-options="{ getterSetter: true }" ns-enter="cellTemplateScope.add({'texto':row.branch.nome},row.branch.obj.pai)">
                    
                    <!-- Se o nó é do tipo capítulo e o atendimento estiver finalizado, só mostro seu nome, sem ser possível editar -->
                    <div ng-if="row.branch.tipo == 'capitulo' && cellTemplateScope.verificaAtcFinalizado()">
                        <span ng-bind="row.branch.nome"></span>
                    </div>

                    <button ng-if="row.branch.tipo === 'capitulo' && row.branch.obj.adicionarNovo==false && !cellTemplateScope.verificaAtcFinalizado()" ng-click="cellTemplateScope.edit({'texto':row.branch.nome},row.branch.obj,this.$modelValue)" type="button" class="action-btn-tree-table"><i class="fas fa-check adicionacapitulohover"></i></button>
                    <button ng-if="row.branch.tipo === 'capitulo' && row.branch.obj.adicionarNovo==true && !cellTemplateScope.verificaAtcFinalizado()" ng-click="cellTemplateScope.add({'texto':row.branch.nome},row.branch.obj,this.$modelValue)" type="button" class="action-btn-tree-table"><i class="fas fa-check"></i></button>

                    <button ng-if="row.branch.tipo === 'capitulo' && row.branch.obj.adicionarNovo==true" ng-click="cellTemplateScope.remove()" class="action-btn-tree-table" type="button"><i class="fas fa-times"></i></button>
                </div>`,

                cellTemplateScope: {
                    add: (textoValorDigitado, obj, scope) => {

                        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de atualizar nome do nó
                        if (this.entity.status == 3 || this.entity.status == 4) return;

                        this.crmNewPropostascapitulosForm(textoValorDigitado, '', this.proposta.proposta, obj, 0); // obj ja vem como guid do pai
                    },
                    remove: () => {
                        this.removerAddLinha();
                    },
                    edit: (textoValorDigitado, obj, scope) => {

                        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de atualizar nome do nó
                        if (this.entity.status == 3 || this.entity.status == 4) return;

                        this.editado = { id: obj.propostacapitulo, fornecedor: obj.fornecedor };

                        let index = this.treetest.findIndex(element => element.propostacapitulo === obj.propostacapitulo);
                        this.crmNewPropostascapitulosForm(textoValorDigitado, obj.propostacapitulo, obj.proposta, obj.pai, index);
                    },
                    //Função que verifica se o atendimento está finalizado ou cancelado ou não
                    verificaAtcFinalizado: () => {
                        return (this.entity.status == 3 || this.entity.status == 4) ? true : false;
                    }
                }

            },
            {
                field: 'tipo',
                displayName: 'Prestador',

                // ver como implementar verificacao da data de acionamento quando vierem os dados reais
                cellTemplate: ` 
                    <div class="container-flex">
                        <div ng-if="row.branch.obj.expirou==1 && row.branch.obj.fornecedor.fornecedor==null" style="display:inline-block;">
                            <i class="fas fa-clock red"></i>
                            <nsj-tooltip ng-if="row.branch.obj.message!=null" tooltip-position="top"><p><strong>ATENÇÃO! </strong><span ng-bind="row.branch.obj.message"></span></p></nsj-tooltip>
                        </div>
                        <div ng-if="row.branch.obj.expirou==2 && row.branch.obj.fornecedor.fornecedor==null" style="display:inline-block;">
                            <i class="fas fa-info-circle yellow"></i>
                            <nsj-tooltip ng-if="row.branch.obj.message!=null" tooltip-position="top"><p><strong>ATENÇÃO! </strong><span ng-bind="row.branch.obj.message"></span></p></nsj-tooltip>
                        </div>
                        <div ng-if="row.branch.obj.fornecedor && row.branch.obj.propostaitem" style="display:inline-block;" ng-click="cellTemplateScope.escolhacliente(row.branch.obj, !row.branch.obj.escolhacliente)" class="div-escolha-cliente" ng-class="row.branch.obj.escolhacliente ? 'escolha-cliente' : ''">
                            <i class="fas fa-user-lock icon-escolha-cliente"></i>
                            <nsj-tooltip tooltip-position="top" ng-if="row.branch.obj.escolhacliente"><p><span>O prestador desse serviço é uma requisição do cliente!</span></p></nsj-tooltip>
                            <nsj-tooltip tooltip-position="top" ng-if="!row.branch.obj.escolhacliente && !cellTemplateScope.verificaAtcFinalizado()"><p><span>Apontar que o prestador desse serviço é uma requisição do cliente!</span></p></nsj-tooltip>
                        </div>  
                        
                        <div style="width:100%" class="pedido-fornecedor-container"
                            ng-if="row.branch.tipo==='item' || (row.branch.tipo==='capitulo' )"
                            ng-disabled="$ctrl.entity.$$__submitting || row.branch.obj.escolhacliente"
                            ng-click="cellTemplateScope.click(this,row)" >
                            <nsj-lookup
                                ng-click="cellTemplateScope.click(this,row)"
                                ng-change="cellTemplateScope.change(row.branch)"
                                ng-if="false && (row.branch.tipo==='item' || (row.branch.tipo==='capitulo' ))"
                                class="lookup-fornecedor lookup-proposta-item"
                                name="fornecedor"
                                id-name="fornecedor"
                                factory="NsFornecedores"  
                                ng-model="row.branch.obj.fornecedor" 
                                template="ns-fornecedores-default"
                                max-label="3"
                                constructor="{  }"
                                config='{ "nomefantasia":"Nome",  }'
                                disabled = "$ctrl.entity.$$__submitting || row.branch.obj.escolhacliente"
                                style="display:inline-block;"
                                ng-init="cellTemplateScope.load(this,row)"
                            >
                            </nsj-lookup>

                            <!-- Apresento se o fornecedor não tiver sido escolhido -->
                            <button tem-permissao="crm_propostasitens_vincularfornecedor" 
                                ng-if="!(row.branch.obj.fornecedor && row.branch.obj.fornecedor.fornecedor) && !cellTemplateScope.verificaAtcFinalizado()" 
                                class="btn btn-action-orange btn-selecionar-fornecedor" 
                                ng-click="cellTemplateScope.abreModalFornecedor(cellTemplateScope, this, row)">

                                <i class="fas fa-building"></i> Selecionar
                            </button>

                            <!-- Apresento se o fornecedor já foi selecionado --> 
                            <div ng-if="row.branch.obj.fornecedor && row.branch.obj.fornecedor.fornecedor" 
                                class="div-fornecedor-selecionado" ng-class="{'tem-permissao': cellTemplateScope.temPermissao('crm_propostasitens_vincularfornecedor') && !cellTemplateScope.verificaAtcFinalizado() }" >

                                <div class="valor" ng-click="cellTemplateScope.abreModalFornecedor(cellTemplateScope, this, row)">
                                    {{ row.branch.obj.fornecedor.nomefantasia }}
                                </div>
                                <div ng-if="!cellTemplateScope.verificaAtcFinalizado()" tem-permissao="crm_propostasitens_vincularfornecedor" class="icon-container" ng-click="cellTemplateScope.desvincularFornecedor(cellTemplateScope, this, row)">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                `,
                cellTemplateScope: {
                    load: (_this, row) => {
                        /*
                        *
                        * Ao carregar a tree é necessário sobrescrever a função clear do lookup de todos os nós
                        * para que seja possível guardar o Id do nó clicado quando se deseja realizar uma desvinculação,
                        * já que quando se clica no "x" que desvincula, não ativa a função on-click.
                        * 
                        */
                        let funcao = _this.$$childHead.$selCtrl.clear;

                        _this.$$childHead.$selCtrl.clear = (a, b) => {
                            this.linhaFornecedorCLicado = row.branch._id_;
                            funcao(a, b);
                        };
                    },
                    click: (_this, row) => {
                        /*
                        * Ao clicar no lookup para realizar uma vinculação, salvar o id do nó clicado.
                        * 
                        */
                        this.linhaFornecedorCLicado = row.branch._id_;
                    },
                    abreModalFornecedor: (cellTemplateScope, _this, row) => {
                        if (!this.temPermissao('crm_propostasitens_vincularfornecedor') || (this.entity.status == 3 || this.entity.status == 4 )){
                            return;
                        }
                        this.linhaFornecedorCLicado = row.branch._id_;
                        let parameters: any = {};

                        //Propriedade que indica se o atendimento tem município de falecimento e de sepultamento
                        parameters.temDoisMunicipios = false;

                        //Caso haja município de falecimento e sepultamento, passo seus codigos nos parâmetros
                        if(this.entity.localizacaomunicipio != null && this.entity.localizacaomunicipiosepultamento != null){
                            
                            //Array que irá com os dois identificadores usados como filtro na modal
                            parameters.municipios = new Array();
                            parameters.municipios[0] = this.entity.localizacaomunicipio.codigo;
                            parameters.municipios[1] = this.entity.localizacaomunicipiosepultamento.codigo;

                            parameters.temDoisMunicipios = true;
                            
                        }

                        //Se houver município na localização e não houverem 2 municípios, passo por parâmetro para a modal usar como filtro
                        if (this.entity.localizacaomunicipio != null && !parameters.temDoisMunicipios) {
                            parameters.municipioibge = this.entity.localizacaomunicipio.codigo;
                        } 
                        //Se não houver município na localização, mas houver município de sepultamento e não houverem 2 municípios, passo por parâmetro para a modal usar como filtro
                        else if (this.entity.localizacaomunicipiosepultamento != null && !parameters.temDoisMunicipios) {
                            parameters.municipioibge = this.entity.localizacaomunicipiosepultamento.codigo;
                        }

                        let modal = this.CrmPropostasitensPretadoresModalService.open(parameters);

                        modal.result.then((retorno: any) => {
                            let fornecedorAtual = row.branch.obj.fornecedor;

                            // Se escolheu um fornecedor e é diferente do fornecedor atual, atribuo e 
                            // chamo change() para seguir fluxo  normal da tela
                            if (retorno.fornecedor && (
                                    // Não tem fornecedor atual
                                    (!fornecedorAtual || !fornecedorAtual.fornecedor) ||
                                    // Tem fornecedor atual e é diferente do escolhido
                                    (fornecedorAtual && fornecedorAtual.fornecedor != retorno.fornecedor.fornecedor)
                                )
                            ) {
                                // Atribuo fornecedor da linha
                                row.branch.obj.fornecedor = retorno.fornecedor;

                                // Chamo change() do cellTemplateScope pra seguir o fluxo normal das alterações
                                cellTemplateScope.change(row.branch);
                            }
                        }).catch((err) => {});
                    },
                    desvincularFornecedor: (cellTemplateScope, _this, row) => {

                        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de desvincular fornecedor
                        if (this.entity.status == 3 || this.entity.status == 4) return;

                        this.linhaFornecedorCLicado = row.branch._id_;
                        // Removo fornecedor da linha
                        row.branch.obj.fornecedor = null;

                        // Chamo change() do cellTemplateScope pra seguir o fluxo normal das alterações
                        cellTemplateScope.change(row.branch);
                    },
                    change: (node: any) => {
                        const temNodeErro = this._nodeSelecionadoErro.has(node._id_);
                        // Verifico se deu erro anteriormente ao atualizar a linha 
                        // caso tenha dado apago o id para que possa atualizar a linha novamente em outra tentativa.
                        if(temNodeErro){
                            this._nodeSelecionadoErro.delete(node._id_);
                            return
                        }

                        this.atualizaRow(node);
                        this.reloadScope(); 
                    },

                    escolhacliente: (entity: any, escolhacliente: boolean = false) => {

                        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não realiza ação de indicar que é escolha do cliente
                        if(this.entity.status == 3 || this.entity.status == 4) return;
                        this.escolhaCliente(entity, escolhacliente);
                    },
                    temPermissao: (action: any) => {
                        return this.temPermissao(action);
                    },
                    //Função que verifica se o atendimento está finalizado ou cancelado ou não
                    verificaAtcFinalizado: () => {
                        return (this.entity.status == 3 || this.entity.status == 4) ? true : false;
                    }
                }
            },

            {
                field: 'tipo',
                displayName: 'Cobertura de Apólice',
                cellTemplate: `

                <!-- familia funcao -->
                <div ng-if="row.branch.tipo == 'itemfamilias' || row.branch.tipo == 'itemfuncoes'">
                    <div ng-if="row.branch.obj.origemApolice === 'sim'">
                        <i class="fas fa-check-circle green"></i> <span ng-bind="row.branch.obj.nomeApolice"></span>
                    </div>
                    <div ng-if="row.branch.obj.origemApolice !== 'sim'">
                        <i class="fas fa-times-circle red"></i> <span ng-bind="row.branch.obj.nomeApolice"></span>
                    </div>
                </div>

                <!-- propostaitem comment-dots -->
                <div ng-if="row.branch.tipo == 'item' && row.branch.obj.observacaocomposicao !== null">
                    <div ng-if="row.branch.obj.todosCobertosApolice === 'sim'" title="Cobertura total com Observações.">
                        <i class="fas fa-comment-dots green"></i>
                    </div>
                    <div ng-if="row.branch.obj.todosCobertosApolice === 'nemTodos'" title="Cobertura parcial com Observações.">
                        <i class="fas fa-comment-dots yellow"></i>
                    </div>
                    <div ng-if="row.branch.obj.todosCobertosApolice === 'nao'" title="Não coberto."> <!-- Possui Observações. -->
                        <i class="fas fa-times-circle red"></i>
                    </div>
                </div>

                <!-- propostaitem comment-slash -->
                <div ng-if="row.branch.tipo == 'item' && row.branch.obj.observacaocomposicao === null" title="something">
                    <div ng-if="row.branch.obj.todosCobertosApolice === 'sim'" title="Cobertura total sem Observações.">
                        <i class="fas fa-comment-slash green"></i>
                    </div>
                    <div ng-if="row.branch.obj.todosCobertosApolice === 'nemTodos'" title="Cobertura parcial sem Observações.">
                        <i class="fas fa-comment-slash yellow"></i>
                    </div>
                    <div ng-if="row.branch.obj.todosCobertosApolice === 'nao'" title="Não coberto."> <!-- Não possui Observações. -->
                        <i class="fas fa-times-circle red"></i>
                    </div>
                </div>
                `,
                cellTemplateScope: {
                    // abrirTarefa: (tarefa: any) => {
                    //     let url = this.linksExternos.gp + '/' + this.$window.nsj.globals.a.tenant + '/gp_tarefas/' + tarefa.tarefa;
                    //     window.open(url, '_blank');
                    // },
                }
            },

            {
                field: null,
                cellTemplate: `
                    <div class='table-btns-actions'
                    ng-class="row.branch.actions.length==0 ? 'naopossuiacoes' : ''">
                        <nsj-actions  >  

                            <nsj-action 
                            
                            ng-repeat="action in row.branch.actions" 
                            ng-click='cellTemplateScope.click(action.method)' 
                            icon='{{action.icon}}'
                            title='{{action.label}}'
                            ng-if="(cellTemplateScope.permissao(action)) && (action.label == 'Excluir Agrupador' && (row.branch.children.length == 0) || action.permission)"
                            >
                            </nsj-action>
                        </nsj-actions>
                    </div>
                    `,
                cellTemplateScope: {
                    click: (acao: any) => {
                        this.traduzMetodo.call(this, acao);
                    },
                    permissao: (action: any) => {
                        if (action.permissionuser != null && action.permissionuser != undefined && action.permissionuser != '') {
                            return this.temPermissao(action.permissionuser);
                        } else {
                            return true;
                        }
                    }
                }
            },
        ];
    }

    temPermissao(chave: any) {
        return this.Usuarios.temPermissao(chave);
    }

    /**
     * Retorna uma string com a data formatada
     * @param data 
     */
    private getDataFormatada(data: any): string {
        let formato = 'DD/MM/YYYY';

        return (data == null ? '-/-/-' : moment(data).format(formato));
    }

    escolhaCliente(entity: any, escolhacliente: boolean) {
        this.busyPedidos = true;
        let entityRequisicao: any = {
            propostaitem: entity.propostaitem,
            negocio: {
                negocio: entity.negocio.negocio
            },
            proposta: {
                proposta: entity.proposta
            },
            escolhacliente: `${escolhacliente}` //Faço isso, senão quando envio false o servidor considera que não enviei o campo.
        };

        this.CrmPropostasitens.propostasitensfornecedorescolhacliente(entityRequisicao)
            .then((dados) => {
                entity.escolhacliente = escolhacliente;
                this.$rootScope.$broadcast('crm_propostasitens_propostasitensfornecedorescolhacliente', {
                    entity: {
                        negocio: entity.negocio.negocio,
                        proposta: entity.proposta
                    }
                })
                this.busyPedidos = false;
            }).catch((err) => {
                this.busyPedidos = false;
                console.error('Não foi possível atualizar escolha do cliente');
            });
    }

    atualizaFornecedorCapitulo () {
        const capitulos = this.treetest.filter(element => element.tipo === 'capitulo');

        for (let index = 0; index < capitulos.length; index++) {
            const node = capitulos[index];

            //Encontra o pai da proposta item e reúne array de irmãos da propostaitem
            const irmaos = this.treetest.filter(element => element._parentId_ === node._parentId_);

            // Verifica se todos os irmãos deste item possuem o mesmo fornecedor selecionado
            const fornecedorIgual = this.temFornecedoresIguais(irmaos, true);

            // Verificando se é possível chamar o método get_parent_branch. Se for, chamo o método, se não for atribuo undefined ao parent
            const parent = Object.keys(this.treePedidos).length == 0 ? undefined : this.treePedidos.get_parent_branch(node);

            /**
             * Caso todos irmãos do item possuam o mesmo fornecedor, é necessário vincular este fornecedor a seu pai, 
             * e verificar se é será necessário vincular ao pai de seu pai recursivamente.
             */
            if(parent) {

                parent.obj["blockedEvent"] = true;
                parent.obj["fornecedor"] = fornecedorIgual ? angular.copy(irmaos[0].obj.fornecedor) : null;
                if (parent.obj.fornecedor) {
                    parent.obj.fornecedor.pai = true
                }
                this.arrPropostascapitulos.forEach(capitulo => {
                    if(capitulo.propostacapitulo == parent.obj.propostacapitulo){
                        capitulo.fornecedor = parent.obj.fornecedor;
                    }
                });
            }
        }
        // this.verificaSeTemPaiRecursivo(parent);
    }

    async atualizaRow(node) {

        if (node.obj.blockedEvent && node._id_ !== this.linhaFornecedorCLicado) {
            setTimeout(function () {
                node.obj.blockedEvent = false;
            }, 1);
            return;
        }

        //Condição para mostrar toaster apenas uma vez 
        if (node._id_ == this.linhaFornecedorCLicado) {
            this.mostrarToaster = true;
        }

        if (node.tipo == 'item') {
            this.changePrestadoraPropostaItem(node)
                .then(() => {
                    //Encontra o pai da proposta item e reúne array de irmãos da propostaitem
                    const irmaos = this.treetest.filter(element => element._parentId_ === node._parentId_);

                    // Verifica se todos os irmãos deste item possuem o mesmo fornecedor selecionado
                    const fornecedorIgual = this.temFornecedoresIguais(irmaos, true);

                    // Verificando se é possível chamar o método get_parent_branch. Se for, chamo o método, se não for atribuo undefined ao parent
                    const parent = Object.keys(this.treePedidos).length == 0 ? undefined : this.treePedidos.get_parent_branch(node);

                    /*
                    * Caso todos irmãos do item possuam o mesmo fornecedor, é necessário vincular este fornecedor a seu pai, 
                    * e verificar se é será necessário vincular ao pai de seu pai recursivamente.
                    */
                   
                    //Só fazer se parent tiver um valor
                    if (parent){
                        parent.obj["blockedEvent"] = true;
                        parent.obj["fornecedor"] = fornecedorIgual ? angular.copy(irmaos[0].obj.fornecedor) : null;
                        if (parent.obj.fornecedor) {
                            parent.obj.fornecedor.pai = true
                        }
                        this.verificaSeTemPaiRecursivo(parent);
                    }
                    this.busyPedidos =  false
                    this.reloadScope()
                })
                .catch((error) => {
                    //SE DER ERRO, COLOCAR O FORNECEDOR DE VOLTA NO LOOKUP
                    const propostaItemSelecionada = this.treePedidos.get_selected_branch();
                    node.obj.fornecedor = angular.copy(propostaItemSelecionada.obj.fornecedor);
                    node.obj.blockedEvent = true;
                    this.busyPedidos = false;
                    this._nodeSelecionadoErro.set(node._id_, angular.copy(node.obj))
                    this.reloadScope();

                    if (error.data) {
                        const message = error.data.message;
                        this.toaster.pop({
                            type: 'error',
                            title: message || 'Ocorreu um erro ao Vincular o Prestador!',
                        });
                        return;

                    } else {
                        console.error(error);
                        return;
                    }
                })
        }
        /*******************                         
         * Se for um capítulo, vinculo ou desvinculo o fornecedor a seus filhos                         
         */
        if (node.tipo == 'capitulo') {

            //atualizar o this.
            this.arrPropostascapitulos.forEach(capitulo => {
                if(capitulo.propostacapitulo == node.obj.propostacapitulo){
                    capitulo.fornecedor = node.obj.fornecedor;
                }
            });

            let propostasItensLote = []
            let filhos = node.children;
            let desvinculando = false;
            // let todosIguais: boolean; //Variavel que verifica se todos os filhos do capítulo tem o mesmo fornecedor
            propostasItensLote['propostasitens'] = this.tree.flatNode(filhos).filter(filho => filho.tipo === 'item');
            propostasItensLote['fornecedor'] = node.obj.fornecedor ? node.obj.fornecedor : null;
            
            if (propostasItensLote['propostasitens'].length > 0 && node._id_ == this.linhaFornecedorCLicado) {
                const todosIguais = this.temFornecedoresIguais(propostasItensLote['propostasitens'], true);
                const fornecedorAtual = todosIguais ? angular.copy(propostasItensLote['propostasitens'][0].obj.fornecedor) : null;

                //Se o fornecedor for nulo, desvinculo o fornecedor, se não for, vinculo o fornecedor
                //crio flag para saber se estou vinculando ou desvinculando.
                try {
                    if (propostasItensLote['fornecedor'] == null) {
                        await this.propostasItensDesvincularFornecedorBulk(propostasItensLote, true);

                        //Broadcast usado no accordion de ficha financeira e prestadora de serviços envolvidas
                        this.$rootScope.$broadcast('vinc_desv_fornecedor');

                    } else {

                        //não vincula caso já possua fornecedor (verificaçao feita também no backend)
                        if (propostasItensLote['fornecedor'] != null) {
                            propostasItensLote['propostasitens'] = propostasItensLote['propostasitens'].filter((element) => {
                                if(element.obj.fornecedor != undefined 
                                    && element.obj.fornecedor != null
                                    && element.obj.fornecedor.fornecedor != undefined
                                    && element.obj.fornecedor.fornecedor != null
                                ) {
                                    return false;
                                } else {
                                    return true;
                                }
                            });
                        }

                        if(propostasItensLote['propostasitens'].length <= 0) {
                            this.toaster.pop({
                                type: 'error',
                                title: 'Não há serviços disponíveis para seleção.'
                            });
                            throw new Error("Não há serviços disponíveis para seleção");
                        }

                        await this.propostasItensVincularFornecedorBulk(propostasItensLote, true);

                        this.arrPropostasitens.filter((propostaItemFilter) => {
                            return propostaItemFilter.propostacapitulo.propostacapitulo == node.obj.propostacapitulo;
                        }).forEach((propostaItemForeach) => {
                            propostaItemForeach.fornecedor = propostasItensLote['fornecedor'];
                        });

                        //Broadcast usado no accordion de ficha financeira e prestadora de serviços envolvidas
                        this.$rootScope.$broadcast('vinc_desv_fornecedor');

                    }
                    this.$rootScope.$broadcast('crm_propostasitens_propostasitensfornecedorescolhacliente', {
                        entity: {
                            negocio: this._atc,
                            proposta: this.proposta.proposta
                        }
                    });
                } catch (error) {
                    this.busyPedidos = false
                    node.obj.fornecedor = fornecedorAtual;
                    //atualizar o this.
                    this.arrPropostascapitulos.forEach(capitulo => {
                        if(capitulo.propostacapitulo == node.obj.propostacapitulo){
                            capitulo.fornecedor = node.obj.fornecedor;
                        }
                    });
                    this._nodeSelecionadoErro.set(node._id_, angular.copy(node.obj))
                    this.reloadScope();
                    return;
                }

            }

            /*********************
            *
            * Atualizar o fornecedor dos filhos desse capítulo
            * 
            * É necessario que esteja ocorrendo o ato de vinculação para que se possa vincular todos os filhos sem questionar.
            * 
            * Caso esteja ocorrendo uma desvinculação, é necessário verificar se o capitulo atual 
            * é o mesmo que foi clicado inicualmente para poder desvincular todos os seus filhos
            * e os filhos deles.
            *
            // ************************/

            if (node.children.length > 0 && desvinculando == false && node._id_ === this.linhaFornecedorCLicado) {
                this.verificarFilhosRecursivo(node, 'vincular');
            }

            
            /**********************
            *  
            * Verificar se capitulo possui pai e vincular recursivamente caso necessário
            * 
            */
            this.verificaSeTemPaiRecursivo(node);
        }
    }

    verificarFilhosRecursivo(node: any, acao:string = "") {
        if (node != null && node != undefined) {
            // Verifica se possui filhos, realiza a ação necessária para cada filho e bloqueia evitando propagação do loop.

            node.children.forEach(filho => {

                //não vincula caso já possua fornecedor (verificaçao feita também no backend)
                if(acao == 'vincular') {

                    if(filho.obj.fornecedor != undefined 
                        && filho.obj.fornecedor != null
                        && filho.obj.fornecedor.fornecedor != undefined
                        && filho.obj.fornecedor.fornecedor != null
                    ) {
                        return;
                    }
                }

                filho.obj.fornecedor = angular.copy(node.obj.fornecedor)
                filho.obj["blockedEvent"] = true;
                if (filho.children.length > 0 && (filho.tipo == 'item' || filho.tipo == 'capitulo')) {
                    this.verificarFilhosRecursivo(filho, acao);
                }
            });
            this.reloadScope();
        }
    }

    verificaSeTemPaiRecursivo(capitulo: any) {
        if (capitulo.obj.blockedEvent) {
            setTimeout(function () {
                capitulo.obj.blockedEvent = false;
            }, 1);
        }

        if (capitulo.obj.pai != null && capitulo.obj.pai != undefined) {
            //Verifica se o objeto recebido possui pai, caso possua, pega o elemento pai da tree
            const parent: any = this.treetest.find(element => element.obj.propostacapitulo === capitulo.obj.pai)
            //Verifica se todos os filhos do pai do objeto recebido possuem o fornecedor igual.
            const fornecedorIgual = this.temFornecedoresIguais(parent.children, true);

            /*
            * Caso todos os filhos possuam fornecedor igual, é necessário vincular a ele o mesmo fornecedor dos filhos
            * e chamar a função recursivamente para verificar 
            * se é necessário vincular o fornecedor ao pai do pai do objeto recebido também.
            */
            if (fornecedorIgual) {
                parent.obj["fornecedor"] = angular.copy(parent.children[0].obj.fornecedor);
                this.verificaSeTemPaiRecursivo(parent);
            } else {
                this.verificaSeTemPaiRecursivo(parent);
                parent.obj["fornecedor"] = null;
            }
        }
    }

    /**
     * Verificando se no array de propostas itens, há pelo menos um fornecedor com orçamento aprovado
     * @param fornecedor 
     */
    private fornecedorTemOrcamentoAprovado(fornecedor) : boolean{

        return this.arrPropostasitens.some((propostaItemSome) => {
            return propostaItemSome.fornecedor != null && fornecedor === propostaItemSome.fornecedor.fornecedor && propostaItemSome.fornecedor.orcamentoaprovado;
        });
    }

    //Verificar porque está rodando duas vezes segunda feira e tentar abolir a requisição desnecessária, por hora está feito, só não perfeito
    async changePrestadoraPropostaItem(node) {
        let proposta = node.obj.proposta.proposta === undefined ? node.obj.proposta : node.obj.proposta.proposta;
        let propostaItem: any = angular.copy(node.obj);
        propostaItem.proposta = {};
        propostaItem.proposta.proposta = proposta;

        this.CrmPropostasitens.constructors = { 'negocio': this._atc, proposta };
        this.busyPedidos = true;

        //Obtendo o endereço da proposta item que está tendo seu encarregado modificado no array de propostas itens
        let propostaItemIndex = this.arrPropostasitens.findIndex((propostaItemFind) => {
            return propostaItemFind.propostaitem == propostaItem.propostaitem; 
        });

        if (propostaItem.fornecedor && propostaItem.fornecedor.fornecedor) {

            //Se o fornecedor não tem orçamento aprovado, posso vincular a proposta item
            if (!this.fornecedorTemOrcamentoAprovado(propostaItem.fornecedor.fornecedor)){
                let erroVincular = false;

                const response = await this.CrmPropostasitens.propostasItensVincularFornecedor(propostaItem)
                    .catch((response: any) => {
                        this.toaster.pop({
                            type: 'error',
                            title: response.data.message
                        });
                        this.busyPedidos = false;
                        erroVincular = true;
                    });

                if (!erroVincular && propostaItemIndex > -1) {
                    this.arrPropostasitens[propostaItemIndex].fornecedor = propostaItem.fornecedor;
                }

                this.treetest.forEach(element => {
                    if (element.obj.propostaitem === propostaItem.propostaitem
                        && !element.obj.propostaitemfuncao
                        && !element.obj.propostaitemfamilia) {
                        element.obj.tarefa = response ? response.tarefa : null;
                    }
                });

                //Broadcast usado no accordion de ficha financeira e prestadora de serviços envolvidas
                this.$rootScope.$broadcast('vinc_desv_fornecedor');

            }

            //Se o fornecedor tem orçamento aprovado, não posso vincular. Mostro um toaster com a mensagem
            else{
                // Chamo função para reorganizar as informações da Tree
                this.organizarDadosTela('capitulo' + this.arrPropostasitens[propostaItemIndex].propostacapitulo.propostacapitulo);

                //toaster informando que não foi possivel vincular
                this.toaster.pop({
                    type: 'error',
                    title: 'Não foi possível vincular o fornecedor! Existem orçamentos aprovados vinculados a ele!'
                });
                this.busyPedidos = false;
            }

        } else {
            let erroDesvincular : boolean = false;

            //Se for desvincular e o fornecedor não tiver orçamento aprovado, tenta desvincular
            if (!this.fornecedorTemOrcamentoAprovado(this.arrPropostasitens[propostaItemIndex].fornecedor.fornecedor)){
                await this.CrmPropostasitens.propostasItensDesvincularFornecedor(propostaItem)
                .then((response: any) => {
                    this.toaster.pop({
                        type: 'success',
                        title: 'O Prestador foi desvinculado com sucesso!'
                    });
                })
                
                .catch((response: any) => {
                    this.toaster.pop({
                        type: 'error',
                        title: response.data.message
                    });
                    this.busyPedidos = false;
                    erroDesvincular = true;
                });

                if(!erroDesvincular){
    
                    if(propostaItemIndex > -1){
                        this.arrPropostasitens[propostaItemIndex].fornecedor = null;

                        this.arrPropostascapitulos.find((capituloFind) => {
                            return capituloFind.propostacapitulo == this.arrPropostasitens[propostaItemIndex].propostacapitulo.propostacapitulo;
                        }).fornecedor = null;
                    }

                    //Broadcast usado no accordion de ficha financeira e prestadora de serviços envolvidas
                    this.$rootScope.$broadcast('vinc_desv_fornecedor');
    
                }

            }
            //Se houver orçamento aprovado vinculado ao fornecedor, mostra toaster falando que não é possível desvincular
            else{
                //Disparar o toaster falando que não foi possível desvincular fornecedor
                this.toaster.pop({
                    type: 'error',
                    title: 'Não foi possível desvincular o fornecedor! Existem orçamentos aprovados vinculados a ele!'
                });
                this.busyPedidos = false;
            }

            // Chamo função para reorganizar as informações da Tree
            this.organizarDadosTela('capitulo' + this.arrPropostasitens[propostaItemIndex].propostacapitulo.propostacapitulo);

        }

        this.reloadScope();
        this.$rootScope.$broadcast('crm_prestadora_propostaitem', propostaItem);
    }


    //Metodo vincular fornecedor em lote nas propostas itens
    propostasItensVincularFornecedorBulk(entity: any, chamadoNoChange = false) {

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        if (entity.obj) {
            entity = entity.obj;
        }

        this.busyPedidos = true;
        let atcId = this._atc
        let propostaId = this.proposta.proposta

        for (let index = 0; index < entity.propostasitens.length; index++) {
            const propostaitem = entity.propostasitens[index];
            entity.propostasitens[index].obj.camposcustomizados = JSON.stringify(propostaitem.obj.camposcustomizados);
        }

        let fornecedor = angular.copy(entity.fornecedor)
        let propostasitens = angular.copy(entity.propostasitens)

        //Objeto a ser passado na requisição, com o fornecedor e as propostasitens a serem alteradas
        let dataToSend = { fornecedor, propostasitens }

        this.loading_deferred = this.$q.defer();
        entity.$$__submitting = true;
        return this.$q((resolve: any, reject: any) => {
            this.$http({
                method: 'post',
                data: dataToSend,
                url: this.nsjRouting.generate('crm_propostasitens_vincular_fornecedor_bulk', { negocio: atcId, proposta: propostaId }),
                timeout: this.loading_deferred.promise
            })
                .then((response: any) => {
                    resolve(response.data);
                    if (chamadoNoChange == null || this.mostrarToaster == true) {
                        this.debounce(this.toaster.pop({
                            type: 'success',
                            title: 'O Prestador foi selecionado com sucesso!'
                        }), 2000);
                    }
                    this.busyPedidos = false;
                })
                .catch((response: any) => {
                    // reject(response);
                    if (chamadoNoChange == null || this.mostrarToaster == true || response) {
                        this.toaster.pop({
                            type: 'error',
                            title: 'Ocorreu um erro ao selecionar o Prestador!'
                        });
                        this.mostrarToaster = false;
                    }
                    this.busyPedidos = false;
                }).finally(() => {
                    entity.$$__submitting = false;
                });
        });

    }

    //Metodo desvincular fornecedor em lote nas propostas itens
    propostasItensDesvincularFornecedorBulk(entity: any, chamadoNoChange = false) {

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        this.busyPedidos = true;
        let atcId = this._atc
        let propostaId = this.proposta.proposta

        let propostasitens = angular.copy(entity.propostasitens)

        //Objeto a ser passado na requisição, com as propostasitens a serem alteradas
        let dataToSend = { propostasitens }

        this.loading_deferred = this.$q.defer();
        entity.$$__submitting = true;
        return this.$q((resolve: any, reject: any) => {
            this.$http({
                method: 'post',
                data: dataToSend,
                url: this.nsjRouting.generate('crm_propostasitens_desvincular_fornecedor_bulk', { negocio: atcId, proposta: propostaId }),
                timeout: this.loading_deferred.promise
            })
                .then((response: any) => {
                    resolve(response.data);
                    if (chamadoNoChange == null || this.mostrarToaster == true) {
                        this.debounce(this.toaster.pop({
                            type: 'success',
                            title: 'O Prestador foi removido!'
                        }), 2000);
                        this.mostrarToaster = false;
                    }

                    for (let index = 0; index < propostasitens.length; index++) {
                        const propostaItemAtual = propostasitens[index];
                        let propostaItemIndex = this.arrPropostasitens.findIndex((propostaItemFind) => {
                            return propostaItemFind.propostaitem == propostaItemAtual.obj.propostaitem; 
                        });
    
                        if(propostaItemIndex > -1){
                            this.arrPropostasitens[propostaItemIndex].fornecedor = null;
                        }
                    }

                    this.busyPedidos = false;
                    // Chamo função para reorganizar as informações da Tree
                    this.organizarDadosTela();
                    this.reloadScope();
                })
                .catch((response: any) => {
                    reject(response);
                    if (chamadoNoChange == null || this.mostrarToaster == true) {
                        this.toaster.pop({
                            type: 'error',
                            title: response.data.message
                        });
                        this.mostrarToaster = false;
                    }
                    this.busyPedidos = false;
                }).finally(() => {
                    entity.$$__submitting = false;
                });
        });
    }

    /**
     * 
     * @param secao secao de histórico => quando não informado exibe o histórico de todas as seções de negócio
     */
    abrirHistoricoModal(secao: string = '') {

        this.busyPedidos = true;
        const parameters = { 'negocio': this.entity.negocio }
        let filters = secao ? { 'secao': secao } : null;

        this.CrmAtcsFormHistoricoService.open(parameters, {}, filters).result
            .then(()=>{
                this.busyPedidos = false;
            })
            .catch(()=>{
                this.busyPedidos = false;
            });

    }

    abrirModal() {

        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de abrir modal
        if (this.entity.status == 3 || this.entity.status == 4) return;

        let modal = this.CrmTemplatespropostasCreatePropostaFormService.open({
            'cliente': this.entity.negociosdadosseguradoras[0].seguradora.cliente,
            'templatepropostagrupo': this.entity.negociosdadosseguradoras[0].produtoseguradora.templatepropostagrupo,
            'identifier': this.entity.negociosdadosseguradoras[0].apolice.templateproposta,
            'seguradoraprodutoseguradora': this.entity.negociosdadosseguradoras[0].produtoseguradora,
            'seguradoraapolice': this.entity.negociosdadosseguradoras[0].apolice.templateproposta,
            'proposta': this.entity.propostas[0],
            'atcsdadosseguradoras': this.entity.negociosdadosseguradoras,
        }, { 'deu certo?': 'não' });
        modal.result.then((entidade: any) => {
            this.busy = true;
            this.CrmTemplatespropostas.criaProposta(entidade);
        })
            .catch((error: any) => {
                if (error === 'fechar') {

                }
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
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
    obterNovoNode(identificador, entity, pai, adicionarNovoNo, index = null) {
        entity.adicionarNovo = adicionarNovoNo;
        const icon = this.obterIcone(identificador);
        const entityId = this.obterIdEntity(identificador, entity);

        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não carrego as ações na tree de pedidos
        const actions = (this.entity.status == 3 || this.entity.status == 4) ? [] : this.obterAcoesDoNode(identificador, entity, index);

        const noNovo = angular.copy(montarNoTree(identificador, entity, entityId, pai, '', actions, icon));
        //adicionar novas propriedades para cada nó da tree
        //  if(noNovo.tipo  === 'propostaitem'){
        //      noNovo.status =  obterStatus(entity);
        //  }

        noNovo.obj["fornecedor"] = angular.copy(entity.fornecedor);
        //familias
        noNovo.obj['origemApolice'] = 'nao';
        if (noNovo.tipo == 'itemfamilias') {
            // if (entity.templatepropostacomposicaofamilia !== undefined && entity.templatepropostacomposicaofamilia !== null && entity.templatepropostacomposicaofamilia.templatecomposicaofamilia !== null) {
            if (
                (entity.templatepropostacomposicaofamilia_templatecomposicaofamilia !== undefined && 
                entity.templatepropostacomposicaofamilia_templatecomposicaofamilia !== null)
                || (entity.templatepropostacomposicaofamilia !== undefined &&
                    entity.templatepropostacomposicaofamilia !== null &&
                    entity.templatepropostacomposicaofamilia.templatecomposicaofamilia !== undefined &&
                    entity.templatepropostacomposicaofamilia.templatecomposicaofamilia !== null)
            ) {
                noNovo.obj['origemApolice'] = 'sim';
                noNovo.obj['nomeApolice'] = entity.templateproposta_nome;
            }
        }
        //funcoes
        if (noNovo.tipo == 'itemfuncoes') {
            // if (entity.templatepropostacomposicaofuncao !== undefined && entity.templatepropostacomposicaofuncao !== null && entity.templatepropostacomposicaofuncao.templatecomposicaofuncao !== null) {
            if (
                (entity.templatepropostacomposicaofuncao_templatecomposicaofuncao !== undefined && 
                entity.templatepropostacomposicaofuncao_templatecomposicaofuncao !== null)
                || (entity.templatepropostacomposicaofuncao !== undefined &&
                    entity.templatepropostacomposicaofuncao !== null &&
                    entity.templatepropostacomposicaofuncao.templatecomposicaofuncao !== undefined &&
                    entity.templatepropostacomposicaofuncao.templatecomposicaofuncao !== null)
            ) {
                noNovo.obj['origemApolice'] = 'sim';
                noNovo.obj['nomeApolice'] = entity.templateproposta_nome;
            }
        }
        return noNovo;
    }

    /**
     * Verificando se o identificador é de um capítulo, item, proposta item função ou proposta item familia
     * @param identificador 
     * @param entity 
     */
    obterIdEntity(identificador, entity) {
        switch (identificador) {
            case 'capitulo':
                return entity.propostacapitulo;
            case 'item':
                return entity.propostaitem;
            case 'itemfuncoes':
                return entity.propostaitemfuncao;
            case 'itemfamilias':
                return entity.propostaitemfamilia;

            case 'treeaddlinha':
                return entity.propostacapitulo;
        }
    }

    /**
     * Carregamento do ícone, dependendo do seu tipo
     * @param identificador
     */
    obterIcone(identificador) {
        switch (identificador) {
            case 'capitulo':
                return 'fas fa-minus';
            case 'item':
                return 'fas fa-minus';
            case 'itemfuncoes':
                return 'fas fa-minus';
            case 'itemfamilias':
                return 'fas fa-minus';

            case 'treeaddlinha':
                return 'fas fa-minus';

        }
    }
    tarefaEmAndamento(tarefa) {

        //Verificando se existe ordem de serviço para a tarefa
        return (tarefa == null) ? false : tarefa.possui_ordemservico;

        //Verificando a situação da tarefa, caso ela exista
        // if(tarefa.situacao == 2) {
        //     return true;
        // }
        // return false;
    }
    /**
     * @description Constrói as ações do node
     * @param proposta 
     * @param propostacapitulo 
     * @param propostaitem 
     * @param propostaindex 
     */
    obterAcoesDoNode(identificador, entity, index = null) {
        let acoes = [];
        switch (identificador) {
            case 'capitulo': {
                /** Impede remoção e edição de Agrupador fixo que será feito em negócios 
                 ***/
                if (entity.principal == true) { //Se for o agrupador principal, não posso excluí-lo
                    acoes = [
                        { 'label': 'Adicionar Serviço', 'permissionuser': 'crm_propostasitens_create', 'permission': true, 'icon': 'fas fa-plus', 'size': 'xs', 'color': 'primary', 'method': `this.crmPropostasitensForm('${entity.proposta}', ${null},'${index}', ${null}, '${entity.propostacapitulo}')` }
                    ];
                }
                return acoes;
            }

            case 'item': {

                //Se o serviço permitir informar a descrição, adiciono ação de editar 
                if(entity.nomeservicoalterado){
                    acoes.push(
                        { 'label': 'Editar', 'permissionuser': 'crm_propostasitens_create', 'permission': !this.tarefaEmAndamento(entity.tarefa), 'icon': 'fas fa-edit', 'size': 'xs', 'color': 'primary', 'method': `this.crmPropostasitensForm('${entity.proposta}','${entity.propostaitem}','${index}',"0",'${entity.propostacapitulo}')` }
                    )
                }

                //Ação de excluir será sempre exibida
                acoes.push(
                    { 'label': 'Excluir', 'permissionuser': 'crm_propostasitens_create', 'permission': !this.tarefaEmAndamento(entity.tarefa), 'icon': 'fas fa-trash-alt', 'size': 'xs', 'color': 'danger', 'method': `this.crmPropostasitensRemover('${entity.proposta}','${entity.propostaitem}','${index}', '${entity.nome}')` }
                );

                if (entity.observacaocomposicao !== null) {
                    acoes.push({ 'label': `Visualizar Observações`, 'permissionuser': 'crm_propostasitens_get', 'permission': true, 'icon': 'fas fa-comment-dots', 'size': 'xs', 'color': 'primary', 'method': `this.modalObservacao('${entity.nome}','${entity.observacaocomposicao}')` });
                }

                return acoes;
            }

            case 'itemfuncoes': {
                acoes = [
                    { 'label': 'Editar', 'permissionuser': 'crm_propostasitensfuncoes_create', 'permission': !this.tarefaEmAndamento(entity.tarefapai), 'icon': 'fas fa-edit', 'size': 'xs', 'color': 'primary', 'method': `this.crmPropostasitensfuncoesForm('${entity.propostaitemfuncao}','${entity.propostaitem}', '${entity.funcao_funcao}', "", '${index}')` },
                    { 'label': 'Excluir', 'permissionuser': 'crm_propostasitensfuncoes_create', 'permission': !this.tarefaEmAndamento(entity.tarefapai), 'icon': 'fas fa-trash-alt', 'size': 'xs', 'color': 'danger', 'method': `this.crmPropostasitensfuncoesRemover('${entity.propostaitem}','${entity.propostaitemfuncao}', '${entity.descricao}')` }
                ];
                return acoes;
            }
            case 'itemfamilias': {
                acoes = [
                    { 'label': 'Editar', 'permissionuser': 'crm_propostasitensfamilias_create', 'permission': !this.tarefaEmAndamento(entity.tarefapai), 'icon': 'fas fa-edit', 'size': 'xs', 'color': 'primary', 'method': `this.crmPropostasitensfamiliasForm('${entity.propostaitemfamilia}','${entity.propostaitem}', '${entity.familia_familia}', "", '${index}')` },
                    { 'label': 'Excluir', 'permissionuser': 'crm_propostasitensfamilias_create', 'permission': !this.tarefaEmAndamento(entity.tarefapai), 'icon': 'fas fa-trash-alt', 'size': 'xs', 'color': 'danger', 'method': `this.crmPropostasitensfamiliasRemover('${entity.propostaitem}','${entity.propostaitemfamilia}', '${entity.descricao}')` }
                ];
                return acoes;
            }

            case 'treeaddlinha': {
                acoes = [];
                return acoes;
            }
        }
    }

    /**
     * Abre modal de observação.
     * @param nome 
     * @param obs 
     */
    modalObservacao(nome, obs) {
        let modal = this.CrmOrcamentosObservacoesSubModalService.open({ 'nome': nome, 'obs': obs }, {});
        modal.result.then((item: any) => {
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busyPedidos = false;
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    /* Chamados pelas ações na tree */
    crmNewPropostascapitulosForm(capitulonome, propostacapitulo, proposta, paiId, index) {
        let capituloObj = {};
        this.busyPedidos = true;
        this.busy = true;
        this._index = index;
        this._proposta = proposta;
        this.CrmPropostascapitulos.constructors = { 'proposta': proposta };

        if ((capitulonome.texto === null) || (capitulonome.texto === undefined) || (capitulonome.texto === '')) {
            this.toaster.pop({
                type: 'error',
                title: 'Nome inválido!',
            });

            this.busyPedidos = false;

            return;
        }

        if (propostacapitulo !== null) {
            capituloObj = {
                pai: paiId,
                nome: capitulonome.texto,
                propostacapitulo: propostacapitulo,
                proposta: proposta
            };
        } else {
            capituloObj = { pai: paiId, nome: capitulonome.texto };
        }
        this.CrmPropostascapitulos.save(capituloObj);
    }

    //recebe um nó e retorna o somatório dos valores dos seus filhos
    somaValorDosFilhos(noPai) {
        const total = noPai.children.map((child => parseFloat(child.valor)))
            .reduce((total: number, valorAtual: number) => {
                return total + valorAtual;
            }, 0);

        return total;
    }

    
    // FUNÇÕES DE PROPOSTAS CAPÍTULOS (AGRUPADOR)
    /**
     * Evento disparado ao salvar, com sucesso, uma proposta capítulo
     */
    ouvirSalvarCapitulo() {
        this.$scope.$on('crm_propostascapitulos_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            let capitulo = isNew ? response.data : entity;

            // Se o capítulo estiver sendo criado, adiciono a lista
            if (isNew) {
                response.data.valor = 0;
                this.arrPropostascapitulos.push(capitulo);
            } else {
                // Se o capítulo estiver sendo editado, eu atualizo o item.
                const propostacapituloIndex = this.arrPropostascapitulos.findIndex((propostacapitulo) => {
                    return propostacapitulo.propostacapitulo == this.editado.id;
                });

                if (propostacapituloIndex > -1) {
                    capitulo.principal = this.arrPropostascapitulos[propostacapituloIndex].principal;
                    capitulo.fornecedor = angular.copy(this.editado.fornecedor);
                    this.arrPropostascapitulos[propostacapituloIndex] = capitulo;
                }
            }
            
            // Chamo função para reorganizar as informações da Tree
            this.organizarDadosTela();

            // Desativo loading
            this.busyPedidos = false;
            this.reloadScope();
        });
    }

    
      
    /**
     * Remove proposta capítulo (Agrupador)
     * @param proposta 
     * @param capitulo 
     * @param index
     */
    crmPropostascapitulosRemover(proposta, capitulo, index, nome) {
        
        let propCapObj = {'id': capitulo, 'nome': nome};
        
        this.CrmPropostascapitulos.constructors = { 'proposta': proposta };
        this.CrmPropostascapitulos.abrirModalExclusao(propCapObj);
        this._capitulo = capitulo;
        this._index = index;
    }

    /**
     * Evento disparado ao remover, com sucesso, uma proposta capítulo
     */
    ouvirRemoverCapitulo() {
        this.$scope.$on('crm_propostascapitulos_deleted', () => {
            this.removerNo(this._capitulo, 'capitulo');

            this.busyPedidos = false;
            this.reloadScope();

            //Mensagem indicando sucesso na exclusão 
            this.toaster.pop({
                type: 'success',
                title: 'O Agrupador foi excluído com sucesso!'
            });

        });
    }

    

    // FUNÇÕES DE PROPOSTA ITEM (SERVIÇO)

    /**
     * Abre modal de criação e edição de propostaitem / composição (Serviço)
     * @param proposta 
     * @param propostaitem 
     */
    crmPropostasitensForm(proposta, propostaitem, index, show = '0', propostacapitulo) {

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        this.busy = true;
        this.busyPedidos = true;
        let showBool = false;
        if (show === '1') {
            showBool = true;
        }
        let propostaitemObj = {};
        if (propostaitem !== null) {
            propostaitemObj = { 'atc': this._atc, propostaitem, showBool };
        }

        let subentity = {
            'proposta': this.proposta, 
            'propostacapitulo': this.arrPropostascapitulos.find((propostacapituloFind) => {
                return propostacapituloFind.propostacapitulo == propostacapitulo;
            }) 
        };

        angular.extend(propostaitemObj, { 'atc': this._atc }, { 'propostacapitulo': propostacapitulo }); //atc é necessario para gerar item contrato eventualmente
        let modal = this.CrmPropostasitensFormService.open(propostaitemObj, subentity);
        
        // Fecha loading após carregar modal
        modal.opened.then(() => {
            this.busy = false;
            this.busyPedidos = false;
        });

        modal.result.then((item: any) => {
            this.busy = true;
            this.busyPedidos = true;
            item.camposcustomizados = JSON.stringify(item.camposcustomizados);
            this.CrmPropostasitens.constructors = { 'negocio': this._atc, proposta };
            this.CrmPropostasitens.save(item);
            this._index = index;
            this._proposta = proposta;
            this._capitulo = propostacapitulo;
            this.busy = true;
            this.busyPedidos = true;

        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.busyPedidos = false;
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
                else {
                    this.busy = false;
                    this.busyPedidos = false;
                }
            });
    }

    /**
     * Evento disparado ao salvar, com sucesso, uma proposta item (Serviço)
     */
    ouvirSalvarPropostaitem() {
        this.$scope.$on('crm_propostasitens_submitted', async (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const propostaitem = isNew ? response.data : entity;

            // Vinculo fornecedor do item pai, caso esteja diferente
            const propostacapitulo = this.arrPropostascapitulos.find((propostacapitulo) => {
                return propostacapitulo.propostacapitulo == (<IPropostaItem>propostaitem).propostacapitulo.propostacapitulo;
            });

            if (propostacapitulo && propostacapitulo.fornecedor && propostacapitulo.fornecedor.fornecedor &&
                (!propostaitem.fornecedor || propostaitem.fornecedor.fornecedor != propostacapitulo.fornecedor.fornecedor)) {

                if(!this.fornecedorTemOrcamentoAprovado(propostacapitulo.fornecedor.fornecedor)){
                    propostaitem.fornecedor = propostacapitulo.fornecedor;
                    const retorno = await this.CrmPropostasitens.propostasItensVincularFornecedor(propostaitem)
                        .catch((response: any) => {
                            this.toaster.pop({
                                type: 'error',
                                title: response.data.message
                            });
                            this.busyPedidos = false;
                        });
                    propostaitem.tarefa = retorno ? retorno.tarefa : null;
                }
            }

            // Se a propostaitem estiver sendo criado, adiciono a lista
            if (isNew) {
                this.arrPropostasitens.push(propostaitem);
            } else {
                // Se o capítulo estiver sendo editado, eu atualizo o item.
                const propostaitemIndex = this.arrPropostasitens.findIndex((propostaitemDaLista) => {
                    return propostaitemDaLista.propostaitem == propostaitem.propostaitem;
                });

                if (propostaitemIndex > -1) {
                    this.arrPropostasitens[propostaitemIndex] = propostaitem;
                }
            }

            if (isNew || (entity && entity.updateFromModalSeguro)) {
                // Chamo função para carregar Dados da tela, porém passando parametro para carregar somente dados da propostaitem
                this.carregarDadosTela({
                    propostaitem: propostaitem
                });
            } else {
                // Chamo função para reorganizar as informações da Tree, informando o id do pai para ser expandido.
                this.organizarDadosTela('capitulo' + propostaitem.propostacapitulo.propostacapitulo);

                // Desativo loading
                this.busyPedidos = false;
                this.reloadScope();
            }
        });
    }

    /**
     * Evento disparado ao salvar, sem sucesso, uma proposta item (Serviço)
     */
    ouvirSalvarPropostaitemError(){
        this.$scope.$on('crm_propostasitens_submit_error', (event: any, args: any) => {
            if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                if ( args.response.data.message === 'Validation Failed' ) {
                    let distinct = (value: any, index: any, self: any) => {
                        return self.indexOf(value) === index;
                    };
                    args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                    let message = this.utilService.parseValidationMessage(args.response.data);
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro de Validação',
                        body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                        bodyOutputType: 'trustedHtml'
                    });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: args.response.data.message
                    });
                }
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Ocorreu um erro ao salvar o item de pedido!'
                });
            }
            this.busy = false;
            this.busyPedidos = false;
        });
    }

    /**
     * Remove propostaitem / composição (Serviço)
     * @param propostacapitulo 
     * @param propostaitem 
     */
    crmPropostasitensRemover(proposta, propostaitem, index, nome) {

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        let propItemObj = {'id': propostaitem, 'nome': nome};

        this.CrmPropostasitens.constructors = { 'negocio': this._atc, 'proposta': this.proposta.proposta };
        this.CrmPropostasitens.abrirModalExclusao(propItemObj);

        this._propostaitem = propostaitem;
        this._index = index;
    }

    /**
     * Evento disparado ao remover, com sucesso, uma proposta item (Serviço)
     */
    ouvirRemoverPropostaitem() {
        this.$scope.$on('crm_propostasitens_deleted', () => {
            // Removo propostaitem da lista
            const propostaitemIndex = this.arrPropostasitens.findIndex((propostaitem) => {
                return propostaitem.propostaitem == this._propostaitem;
            });

            this.arrPropostasitens.splice(propostaitemIndex, 1);

            // Removo propostasitensfamilias do propostaitem
            this.arrPropostasitensfamilias = this.arrPropostasitensfamilias.filter((propostaitemfamilia) => {
                return propostaitemfamilia.propostaitem != this._propostaitem;
            });

            // Removo propostasitensfuncoes do propostaitem
            this.arrPropostasitensfuncoes = this.arrPropostasitensfuncoes.filter((propostaitemfuncao) => {
                return propostaitemfuncao.propostaitem != this._propostaitem;
            });

            // Chamo função para reorganizar as informações da Tree
            this.organizarDadosTela();

            // Desativo loading
            this.busyPedidos = false;
            this.reloadScope();

            //Mensagem indicando sucesso na exclusão 
            this.toaster.pop({
                type: 'success',
                title: 'O Serviço foi excluído com sucesso!'
            });

        });
    }

    /**
     * Evento disparado ao remover, sem sucesso, uma proposta item (Serviço)
     */
    ouvirRemoverPropostaitemError() {
        this.$scope.$on('crm_propostasitens_delete_error', (entity, response) => {
            this.toaster.pop({
                type: 'error',
                title: response.response.data.message
            });

            this.busyPedidos = false;
        });
    }

    // FUNÇÕES DE PROPOSTA ITEM FAMÍLIA (PRODUTO)
    
    /**
     * Abre modal de criação e edição de proposta item familia (Produto)
     */
    crmPropostasitensfamiliasForm(propostaitemfamilia, propostaitem, familia, paiId, index) {

        // descomentar para ativar o loading ao salvar propostaitemfuncao
        // depois que der merge com o conserto que foi feito na tarefa do matheus
        // pois está quebrando o retorno da requisição, que vem sem o objeto criado.
        // this.busyPedidos = true;

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        let propostaItemFamiliaObj = {};
        
        let objPropItem = this.arrPropostasitens.find(propItem => propItem.propostaitem == propostaitem);
        let negocio = objPropItem.negocio.negocio; 
        let proposta = objPropItem.proposta; 

        let propostaitemfamiliaEntity = {};
        if (propostaitemfamilia !== null) {
            propostaitemfamiliaEntity = this.arrPropostasitensfamilias.find(item => item.propostaitemfamilia == propostaitemfamilia);
            propostaItemFamiliaObj = { propostaitemfamilia: propostaitemfamilia, propostaitem: propostaitem, familia: familia, negocio: negocio, proposta: proposta }
        } else {
            propostaItemFamiliaObj = {negocio: negocio, proposta: proposta};
        }
        
        let modal = this.CrmPropostasitensfamiliasFormService.open(propostaItemFamiliaObj, propostaitemfamiliaEntity);
        modal.result.then((propostaitemfamilia: any) => {
            this.busy = true;
            this.busyPedidos = true;
            this.CrmPropostasitensfamilias.constructors = { 'propostaitem': propostaitem };
            this.CrmPropostasitensfamilias.save(propostaitemfamilia);
            this._index = index;
            this._propostaitem = propostaitem;
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.busyPedidos = false;
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    /**
     * Evento disparado ao salvar, com sucesso, uma proposta item família (Produto)
     */
    ouvirSalvarPropostaitemfamilia() {
        this.$scope.$on('crm_propostasitensfamilias_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const propostaitemfamilia = isNew ? response.data : entity;

            // Se a propostaitem estiver sendo criado, adiciono a lista
            if (isNew) {
                this.arrPropostasitensfamilias.push(propostaitemfamilia);
            } else {
                // Se o capítulo estiver sendo editado, eu atualizo o item.
                const propostaitemfamiliaIndex = this.arrPropostasitensfamilias.findIndex((propostaitemfamiliaDaLista) => {
                    return propostaitemfamiliaDaLista.propostaitemfamilia == propostaitemfamilia.propostaitemfamilia;
                });

                if (propostaitemfamiliaIndex > -1) {
                    this.arrPropostasitensfamilias[propostaitemfamiliaIndex] = propostaitemfamilia;
                }
            }

            // Chamo função para reorganizar as informações da Tree, informando o id do pai para ser expandido.
            this.organizarDadosTela('item' + propostaitemfamilia.propostaitem);

            // Desativo loading
            this.busyPedidos = false;
            this.reloadScope();
        });
    }

    /**
     * Evento disparado ao salvar, sem sucesso, uma proposta item família (Produto)
     */
    ouvirSalvarPropostaitemfamiliaError() {
        this.$scope.$on('crm_propostasitensfamilias_submit_error', (entity, response) => {
            this.toaster.pop({
                type: 'error',
                title: response.response.data.message
            });

            //recarregar ficha financeira
            this.recarregaFichaFinanceira();

            this.busyPedidos = false;

        });
    }

    /**
     * Remove proposta item familia (Produto)
     * @param propostaitem 
     * @param propostaitemfamilia 
     */
    crmPropostasitensfamiliasRemover(propostaitem, propostaitemfamilia, nome) {

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        let objPropItem = this.arrPropostasitens.find(propItem => propItem.propostaitem == propostaitem);
        let negocio = objPropItem.negocio.negocio; 
        let proposta = objPropItem.proposta;

        let propItemFamObj = {'id': propostaitemfamilia, 'nome': nome, negocio: negocio, proposta: proposta};

        this.CrmPropostasitensfamilias.constructors = { 'propostaitem': propostaitem };
        this.CrmPropostasitensfamilias.abrirModalExclusao(propItemFamObj);

        this._propostaitemfamilia = propostaitemfamilia;
    }

    /**
     * Evento disparado ao remover, com sucesso, uma proposta item família (Produto)
     */
    ouvirRemoverPropostaitemfamilia() {
        this.$scope.$on('crm_propostasitensfamilias_deleted', () => {
            // Removo propostaitem da lista
            const propostaitemfamiliaIndex = this.arrPropostasitensfamilias.findIndex((propostaitemfamilia) => {
                return propostaitemfamilia.propostaitemfamilia == this._propostaitemfamilia;
            });

            this.arrPropostasitensfamilias.splice(propostaitemfamiliaIndex, 1);

            // Chamo função para reorganizar as informações da Tree
            this.organizarDadosTela();

            // Desativo loading
            this.busyPedidos = false;
            this.reloadScope();

            //Mensagem indicando sucesso na exclusão 
            this.toaster.pop({
                type: 'success',
                title: 'O Produto foi excluído com sucesso!'
            });

            //recarregar ficha financeira
            this.recarregaFichaFinanceira();
        });
    }

    /**
     * Evento disparado ao remover, sem sucesso, uma proposta item família (Produto)
     */
    ouvirRemoverPropostaitemfamiliaError() {
        this.$scope.$on('crm_propostasitensfamilias_delete_error', (entity, response) => {
            this.toaster.pop({
                type: 'error',
                title: response.response.data.message
            });

            this.busyPedidos = false;
        });
    }

    // FUNÇÕES DE PROPOSTA ITEM FUNÇÃO (PROFISSIONAL)

    /**
     * Abre modal de criação e edição de proposta item funcao (Profissional)
     */
    crmPropostasitensfuncoesForm(propostaitemfuncao, propostaitem, funcao, paiId, index) {
        // descomentar para ativar o loading ao salvar propostaitemfuncao
        // depois que der merge com o conserto que foi feito na tarefa do matheus
        // pois está quebrando o retorno da requisição, que vem sem o objeto criado.
        // this.busyPedidos = true;

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        let propostaItemFuncaoObj = {};

        let objPropItem = this.arrPropostasitens.find(propItem => propItem.propostaitem == propostaitem);
        let negocio = objPropItem.negocio.negocio; 
        let proposta = objPropItem.proposta;

        let propostaitemfuncaoEntity = {};
        if (propostaitemfuncao !== null) {
            propostaitemfuncaoEntity = this.arrPropostasitensfuncoes.find(item => item.propostaitemfuncao == propostaitemfuncao);
            propostaItemFuncaoObj = { propostaitemfuncao: propostaitemfuncao, propostaitem: propostaitem, funcao: funcao, negocio: negocio, proposta: proposta }
        } else {
            propostaItemFuncaoObj = {negocio: negocio, proposta: proposta};
        }
        let modal = this.CrmPropostasitensfuncoesFormService.open(propostaItemFuncaoObj, propostaitemfuncaoEntity);
        modal.result.then((propostaitemfuncao: any) => {
            this.busy = true;
            this.busyPedidos = true;
            this.CrmPropostasitensfuncoes.constructors = { 'propostaitem': propostaitem };
            this.CrmPropostasitensfuncoes.save(propostaitemfuncao);
            this._index = index;
            this._propostaitem = propostaitem;
        }).catch((error: any) => {
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.busy = false;
                this.busyPedidos = false;
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }
    
    /**
     * Evento disparado ao salvar, com sucesso, uma proposta item função (Profissional)
     */
    ouvirSalvarPropostaitemfuncao() {
        this.$scope.$on('crm_propostasitensfuncoes_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const propostaitemfuncao = isNew ? response.data : entity;

            // Se a propostaitem estiver sendo criado, adiciono a lista
            if (isNew) {
                this.arrPropostasitensfuncoes.push(propostaitemfuncao);
            } else {
                // Se o capítulo estiver sendo editado, eu atualizo o item.
                const propostaitemfuncaoIndex = this.arrPropostasitensfuncoes.findIndex((propostaitemfuncaoDaLista) => {
                    return propostaitemfuncaoDaLista.propostaitemfuncao == propostaitemfuncao.propostaitemfuncao;
                });

                if (propostaitemfuncaoIndex > -1) {
                    this.arrPropostasitensfuncoes[propostaitemfuncaoIndex] = propostaitemfuncao;
                }
            }

            // Chamo função para reorganizar as informações da Tree
            this.organizarDadosTela('item' + propostaitemfuncao.propostaitem);

            // Desativo loading
            this.busyPedidos = false;
            this.reloadScope();
        });
    }

    /**
     * Evento disparado ao salvar, sem sucesso, uma proposta item função (Profissional)
     */
    ouvirSalvarPropostaitemfuncaoError() {
        this.$scope.$on('crm_propostasitensfuncoes_submit_error', (entity, response) => {
            this.toaster.pop({
                type: 'error',
                title: response.response.data.message
            });

            this.busyPedidos = false;
        });
    }
    
    /**
     * Remove proposta item função (Profissional)
     * @param propostaitem
     * @param propostaitemfuncao 
     */
    crmPropostasitensfuncoesRemover(propostaitem, propostaitemfuncao, nome) {

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        let objPropItem = this.arrPropostasitens.find(propItem => propItem.propostaitem == propostaitem);
        let negocio = objPropItem.negocio.negocio; 
        let proposta = objPropItem.proposta;

        let propItemFunObj = {'id': propostaitemfuncao, 'nome': nome, negocio: negocio, proposta: proposta};

        this.CrmPropostasitensfuncoes.constructors = { 'propostaitem': propostaitem };
        this.CrmPropostasitensfuncoes.abrirModalExclusao(propItemFunObj);

        this._propostaitemfuncao = propostaitemfuncao;
    }

    /**
     * Evento disparado ao remover, com sucesso, uma proposta item função (Profissional)
     */
    ouvirRemoverPropostaitemfuncao() {
        this.$scope.$on('crm_propostasitensfuncoes_deleted', () => {
            // Removo propostaitem da lista
            const propostaitemfuncaoIndex = this.arrPropostasitensfuncoes.findIndex((propostaitemfuncao) => {
                return propostaitemfuncao.propostaitemfuncao == this._propostaitemfuncao;
            });

            this.arrPropostasitensfuncoes.splice(propostaitemfuncaoIndex, 1);

            // Chamo função para reorganizar as informações da Tree
            this.organizarDadosTela();

            // Desativo loading
            this.busyPedidos = false;
            this.reloadScope();

            //Mensagem indicando sucesso na exclusão 
            this.toaster.pop({
                type: 'success',
                title: 'O Profissional foi excluído com sucesso!'
            });

            //Recarregar ficha financeira
            this.recarregaFichaFinanceira();
        });
    }

    /**
     * Evento disparado ao remover, sem sucesso, uma proposta item função (Profissional)
     */
    ouvirRemoverPropostaitemfuncaoError() {
        this.$scope.$on('crm_propostasitensfuncoes_delete_error', (entity, response) => {
            this.toaster.pop({
                type: 'error',
                title: response.response.data.message
            });

            this.busyPedidos = false;
        });
    }

    /**
     * dispara evento crm_atcsfichafinanceira_refresh em ficha-financeira.ts
     */
    recarregaFichaFinanceira() {
        this.$rootScope.$broadcast('crm_atcsfichafinanceira_refresh', {
        });
    }

    /**
     * Abre o modal de edição da proposta
     * @param subentity 
     */
    crmPropostasFormEdit(subentity: any) {
        let parameter = { 'atc': subentity.negocio, 'identifier': subentity.proposta };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.CrmPropostasFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;

                for (key in this.entity.proposta) {
                    if ((this.entity.proposta.proposta !== undefined && this.entity.proposta.proposta === subentity.proposta)
                        || (this.entity.proposta.$id !== undefined && this.entity.proposta.$id === subentity.$id)) {
                        this.entity.proposta = subentity;
                    }
                }

            })
            .catch((error: any) => {
                this.busy = false;
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    /**
     * Abre o modal para cadastro da proposta
     */
    crmPropostasForm() {
        let modal = this.CrmPropostasFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;

            if (this.entity.proposta === undefined) {
                this.entity.proposta = [subentity];
            } else {
                this.entity.proposta = subentity;
            }

            this.busy = true;
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    /**
     * Adiciona o pedido ao atendimento quando o botão é clicado
     */
    adicionaPedido() {
        this.busy = true;
        this.CrmPropostas.constructors = { 'negocio': this.entity.negocio };
        this.CrmPropostas.save({});

    }

    onAddPedido() {
        this.$scope.$on('crm_propostas_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Pedido foi criado com sucesso!'
            });
            this.proposta = args.response.data;
            this.proposta.capitulos = [];
            this.busy = false;
        });

        this.$scope.$on('crm_propostas_submit_error', (event: any, args: any) => {
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao inserir o pedido!'
            });
            this.busy = false;
        });
    }

    /**
     * Carrega capítulos de uma proposta
     * @param propostaindex 
     * @param proposta 
     */
    async getCapitulos(propostaindex, proposta) {

        this.proposta.capitulos = [];

        const { data: capitulos }: any = await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate('crm_propostascapitulos_index', { 'proposta': proposta }, true, true)
        })

        for (const capitulo of capitulos) {
            /** 
             * @todo condicional para remoção: não ser pai  
             * */
            const pai = capitulo['pai'] == null ? null : 'capitulo' + capitulo.pai;
            const adicionarNovoNo = false;
            const noNovo = this.obterNovoNode('capitulo', capitulo, pai, adicionarNovoNo, propostaindex)

            // passo minha lista `treeTest` apenas fazer push de itens da proposta;
            await this.obterPropostasItens(capitulo, propostaindex)
            this.treetest.push(noNovo);

            //Percorrendo a treetest buscando os filhos do capítulo e salvando seus valores em um somador
            var valorTotal = 0;
            for (var i = 0; i < this.treetest.length; i++) {
                if (this.treetest[i]._parentId_ == noNovo._id_) {
                    valorTotal += parseFloat(this.treetest[i].valor);
                }

            }

            //Após somar os valores dos filhos, percorro a treetest para encontrar o capítulo pai e atribuo o somador ao seu valor
            for (var i = 0; i < this.treetest.length; i++) {

                if (this.treetest[i]._id_ == noNovo._id_) {
                    this.treetest[i].valor = valorTotal;
                    break;
                }

            }
        }
        this.atualizarCoberturaApolicePropostaItem();
        this.reloadScope();
        this.busy = true;
        this.busyPedidos = false;
    }

    /**
     * Função que atualiza a definição de cobertura da apolice para propostasitens,
     * baseado nas definições das funcoes e familias.
     */
    atualizarCoberturaApolicePropostaItem() {
        this.treetest.forEach(propostaitem => {
            let qtdCobertos = 0;
            let qtdNaoCobertos = 0;
            let possuiApolice = false;
            if (propostaitem.tipo !== 'item') return;
            if(propostaitem.obj.id_apolice !== undefined && propostaitem.obj.id_apolice !== null){
                possuiApolice = true;
            }
            this.treetest.forEach(subitem => {
                if (subitem.tipo !== 'itemfamilias' && subitem.tipo !== 'itemfuncoes') return;
                if (propostaitem.obj.propostaitem !== subitem.obj.propostaitem) return;

                if (subitem.obj.origemApolice === 'sim') {
                    qtdCobertos++;
                } else if (subitem.obj.origemApolice === 'nao') {
                    qtdNaoCobertos++
                }
            });

            if (qtdCobertos > 0 && qtdNaoCobertos > 0) {
                // tem cobertos e não cobertos
                propostaitem.obj['todosCobertosApolice'] = 'nemTodos';
            } else if (qtdCobertos > 0 && qtdNaoCobertos == 0) {
                // coberto
                propostaitem.obj['todosCobertosApolice'] = 'sim';
            } else if (qtdNaoCobertos > 0 && qtdCobertos == 0) {
                // não coberto
                propostaitem.obj['todosCobertosApolice'] = 'nao';
            } else if (possuiApolice === true) {
                // não tem itens mas é coberto
                propostaitem.obj['todosCobertosApolice'] = 'sim';
            } else {
                // não tem itens e não coberto
                propostaitem.obj['todosCobertosApolice'] = 'nao';
            }
        });
    }


    atualizarVinculoCapituloNaTela(capitulo: any) {
        //verificar se em todas as propostas itens o campo fornecedor é igual
        let fornecedorIgual = true;
        let fornecedor;
        let filhos = angular.copy(capitulo.children);

        if (filhos.length > 0) {
            //Verificando se todas as propostasitens tem o mesmo fornecedor
            fornecedorIgual = this.temFornecedoresIguais(filhos, fornecedorIgual)
            //Se todos os fornecedores forem iguais, o id do fornecedor recebe o id de uma das propostasitens, senão, recebo null
            fornecedor = fornecedorIgual ? angular.copy(filhos[0].obj.fornecedor) : null;

            //adicionando o fornecedor ao nó do capítulo na treetest
            for (var i = 0; i < this.treetest.length; i++) {
                if (this.treetest[i]._id_ == capitulo._id_) {
                    this.treetest[i].obj["fornecedor"] = angular.copy(fornecedor);
                    capitulo.obj.fornecedor = angular.copy(fornecedor);
                    break;
                }
            }
        }


    }

    reloadScope() {
        const treeFormated = this.tree.getArvore(this.treetest);
        this.proposta.capitulos = treeFormated;
        this.$scope.$applyAsync();
    }


    temFornecedoresIguais(filhos = [], fornecedorIgual: boolean) {
        filhos.forEach(filho => {
            if (filho.tipo == 'capitulo') {
                this.atualizarVinculoCapituloNaTela(filho);
            }

            if (fornecedorIgual && (filho.tipo == 'item' || filho.tipo == 'capitulo')) {
                //Se o fornecedor for nulo ou se for diferente de um nó irmão, tem fornecedores diferentes 
                if (
                    (filho.obj.fornecedor === null || filho.obj.fornecedor === undefined)
                    || (filho.obj.fornecedor.fornecedor !== filhos[0].obj.fornecedor.fornecedor)
                ) {
                    fornecedorIgual = false;
                }
            }

        });

        return fornecedorIgual;
    }

    traduzMetodo(metodo) {
        return eval(metodo);
    };
    /**
     * Insere na lista os itens Proposta 
     * @param item 
     * @param lista 
     */
    async obterPropostasItens(entity: any, indexProposta: number) {
        const capitulo = angular.copy(entity);
        const propostaItens: any = await this.carregaPropostasitens(capitulo.proposta, this._atc, capitulo.propostacapitulo);
        this.propostasitensqtde += propostaItens.length;
        let propostaItem: any;
        for (const item of propostaItens) {
            propostaItem = await this.CrmPropostasitens.get(this._atc, capitulo.proposta, item.propostaitem);
            await this.obterPropostasItensFuncoes(angular.copy(propostaItem));
            await this.obterPropostasItensFamilias(angular.copy(propostaItem));
            const paiCapitulo = 'capitulo' + capitulo.propostacapitulo;
            propostaItem.proposta = angular.copy(capitulo.proposta);
            const adicionarNovoNo = false;
            propostaItem = this.calculodePrazo(propostaItem);
            const no = this.obterNovoNode('item', propostaItem, paiCapitulo, adicionarNovoNo, indexProposta);
            this.treetest.push(no);
        }

        this.reloadScope();
    }

    /**
     * Insere na lista os itens funções
     * @param entity 
     */
    async obterPropostasItensFuncoes(entity: any) {
        const { propostaitem, proposta } = entity;
        const propostaItensFuncoes: any = await this.carregaPropostasitensFuncoes(propostaitem);

        for (let itemFuncoes of propostaItensFuncoes) {
            const paiItem = 'item' + itemFuncoes.propostaitem;
            itemFuncoes.descricao = itemFuncoes.funcao_descricao;
            itemFuncoes.tarefapai = angular.copy(entity.tarefa);
            itemFuncoes = { ...itemFuncoes, ...itemFuncoes.funcao }
            itemFuncoes.proposta = angular.copy(proposta);

            //concatenando a quantidade ao nome da funcao
            if (itemFuncoes.nome || itemFuncoes.descricao) {
                if (itemFuncoes.nome) {
                    itemFuncoes.nome = itemFuncoes.quantidade + 'x ' + itemFuncoes.nome;
                }
                else {
                    itemFuncoes.descricao = itemFuncoes.quantidade + 'x ' + itemFuncoes.descricao;
                }
            }

            const adicionarNovoNo = false;
            const no = this.obterNovoNode('itemfuncoes', itemFuncoes, paiItem, adicionarNovoNo);
            this.treetest.push(no);
        }
        this.reloadScope();
    }

    /**
     * Insere na lista os itens famílias
     * @param entity 
     */
    async obterPropostasItensFamilias(entity: any) {
        const { propostaitem, proposta } = entity;
        const propostaItensFamilias: any = await this.carregaPropostasitensFamilias(propostaitem);

        for (let itemFamilias of propostaItensFamilias) {
            const paiItem = 'item' + itemFamilias.propostaitem;
            itemFamilias.descricao = itemFamilias.familia.descricao;
            itemFamilias.tarefapai = angular.copy(entity.tarefa);
            itemFamilias = { ...itemFamilias, ...itemFamilias.familia }
            itemFamilias.valor = itemFamilias.valor || itemFamilias.familia.valor;
            itemFamilias.proposta = angular.copy(proposta);

            //concatenando a quantidade ao nome do produto
            if (itemFamilias.nome || itemFamilias.descricao) {
                if (itemFamilias.nome) {
                    itemFamilias.nome = itemFamilias.quantidade + 'x ' + itemFamilias.nome;
                }
                else {
                    itemFamilias.descricao = itemFamilias.quantidade + 'x ' + itemFamilias.descricao;
                }
            }

            const adicionarNovoNo = false;
            const no = this.obterNovoNode('itemfamilias', itemFamilias, paiItem, adicionarNovoNo);
            this.treetest.push(no);
        }

        this.reloadScope();
    }

    /**
     * Carrega itens da proposta (composições) 
     * @param propostacapitulo 
     */
    carregaPropostasitens(proposta, atc, propostacapitulo) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_index', { negocio: atc, proposta, propostacapitulo }, true, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Carrega propostas itens funções
     * @param propostaitem 
     */
    carregaPropostasitensFuncoes(propostaitem) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_funcoes_index', { propostaitem }, true, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }
    /**
     * Carrega propostas itens famílias
     * @param propostaitem 
     */
    carregaPropostasitensFamilias(propostaitem) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_familias_index', { propostaitem }, true, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }
    /* ---- */

    /* Manipulação da tree */


    isBusyPedidos() {
        return this.busyPedidos;
    }

    /* Calculo de prazo para exibição das horas faltantes para seleção de um fornecedor de PropostaItens na tree de pedidos.
        Existe uma diferença entre a hora do servidor e a hora da aplicação, foi implementada uma função para esse cálculo
        E flags que permitem exibição correta dos relógios
        0 - Nenhum a exibir
        1 - Expirou o prazo
        2 - Prazo em alerta de expirar!*/
    calculodePrazo(propostaItem) {
        let prazoNavegador: any;
        let alertaNavegador: any;
        let hoje = moment().format('YYYY/MM/DD hh:mm:ss');

        let horaserver = moment(propostaItem.agora);
        let horanavegador = moment(new Date());
        let duration = moment.duration(horaserver.diff(horanavegador));
        let diferencaMinutos = duration.asMinutes();

        if (diferencaMinutos > 0) {
            prazoNavegador = moment(propostaItem.prazo).subtract(diferencaMinutos, 'm').format('YYYY/MM/DD hh:mm:ss');
            alertaNavegador = moment(propostaItem.alerta).subtract(diferencaMinutos, 'm').format('YYYY/MM/DD hh:mm:ss');
        }
        if (diferencaMinutos < 0) {
            prazoNavegador = moment(propostaItem.prazo).add(diferencaMinutos, 'm').format('YYYY/MM/DD hh:mm:ss');
            alertaNavegador = moment(propostaItem.alerta).add(diferencaMinutos, 'm').format('YYYY/MM/DD hh:mm:ss');
        }
        else {
            prazoNavegador = moment(propostaItem.prazo).format('YYYY/MM/DD hh:mm:ss');
            alertaNavegador = moment(propostaItem.alerta).format('YYYY/MM/DD hh:mm:ss');
        }

        let prazoNavegadorFormatado = moment(prazoNavegador).format('DD/MM/YYYY hh:mm:ss');

        if (hoje >= prazoNavegador) {
            propostaItem.message = "O prazo de seleção de prestador expirou em: " + prazoNavegadorFormatado;
            propostaItem.expirou = 1;
        }
        else if (hoje < prazoNavegador && hoje > alertaNavegador) {
            propostaItem.message = "O prestador precisa ser selecionado até: " + prazoNavegadorFormatado;
            propostaItem.expirou = 2;
        }
        else {
            propostaItem.expirou = 0
        }
        return propostaItem;
    }

    /**
     * Abre a edição do projeto relacionado ao atendimento no sistema Gestão de Serviços
     * @param url
     */
    abrirEdicaoProjetoGP(url: string) {
        window.open(url, '_blank');
    }
}

type treeExpand = {
    field: string;
    displayName: string;
    sortable: boolean;
    filterable: boolean;
    cellTemplate?: string;
    cellTemplateScope?: any;
};

interface TreeControl {
    expand_all?: () => any
    collapse_all?: () => any
    get_first_branch?: () => any
    select_first_branch?: () => any
    get_selected_branch?: () => any
    get_parent_branch?: (b) => any
    select_branch?: (b) => any
    get_children?: (b) => any
    select_parent_branch?: (b) => any
    add_branch?: (parent, new_branch) => any
    add_root_branch?: (new_branch) => any
    expand_branch?: (b) => any
    collapse_branch?: (b) => any
    get_siblings?: (b) => any
    get_next_sibling?: (b) => any
    get_prev_sibling?: (b) => any
    select_next_sibling?: (b) => any
    select_prev_sibling?: (b) => any
    get_first_child?: (b) => any
    get_closest_ancestor_next_sibling?: (b) => any
    get_next_branch?: (b) => any
    select_next_branch?: (b) => any
    last_descendant?: (b) => any
    get_prev_branch?: (b) => any
    select_prev_branch?: (b) => any
}

interface IProposta {
    proposta?: string;
}
interface IPropostaCapitulo {
    propostacapitulo?: string;
    proposta?: string;
    nome?: string;
    pai?: string;
    valor?: number;
    fornecedor?: any;
    principal?: boolean;
}
interface IPropostaItem {
    propostaitem?: string;
    proposta?: string;
    propostacapitulo?: IPropostaCapitulo;
    nome?: string;
    fornecedor?: IFornecedor;
    tarefa?: any;
    agora?: any;
    prazo?: any;
    alerta?: any;
    valor?: number;
    possuifamilias?: boolean;
    possuifuncoes?: boolean;
    negocio?: any;
}
interface IPropostasItemFamilia {
    propostaitemfamilia?: string;
    propostaitem?: string;
    valor?: number;
    quantidade?: number;
}
interface IPropostasItemFuncao {
    propostaitemfuncao?: string;
    propostaitem?: string;
}

interface IFornecedor {
    fornecedor : string;
    orcamentoaprovado?: boolean;
}


