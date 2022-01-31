import angular = require('angular');
import { Tree } from '../../Commons/tree';
import { montarNoTree } from '../../Commons/utils';

export class CrmFichafinanceiraFormModalController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        'constructors',
        'Tree',
        'nsjRouting',
        '$http',
        '$scope'
    ];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    public expanding_property: treeExpand;
    public col_defs: any;
    public busy: boolean = true;
    public treetest = [];
    public encarregado: string;// = this.entity[0].fornecedor; //Como todas as propostasitens tem o mesmo prestador, pego o nome do primeiro
    public lstPropostasItens: any;
    public somaDesconto: number = 0;
    public somaAcrescimo: number = 0;
    public somaValor: number = 0;
    public valorFinal: number = 0;

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        public tree: Tree,
        public nsjRouting: any,
        public $http: any,
        public $scope: any
    ) {}

    isBusy (){
        return this.busy;
    }

    $onInit() {
        this.busy = true;
        this.initColDef();
        this.preenchePropostasItens();
    }

    initColDef() {
    
        this.expanding_property = {
            field: 'nomeNo',
            displayName: 'Serviços e Produtos',
            sortable: true,
            filterable: true,
        }    

        /* carrega as colunas */
        this.col_defs = [
            {
                field: 'orcamentoReal',
                displayName: 'Valor',
                cellTemplate: `
                    <span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.orcamentoReal | currency : 'R$ ' : 2) || '-'"></span>`,
            },
            {
                field: 'desconto',
                displayName: 'Desconto',
                cellTemplate: `
                    <strong>- </strong><span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.desconto | currency : 'R$ ' : 2) || '-'"></span>`,
                
            },
            {
                field: 'acrescimo',
                displayName: 'Acréscimo',
                cellTemplate: `
                    <strong>+ </strong><span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.acrescimo | currency : 'R$ ' : 2) || '-'"></span>`,           
            },

            {
                field: null,
                cellTemplate: `
                    <div class='table-btns-actions'>
                        <nsj-actions  >  
                        <nsj-action 
                        ng-repeat="action in row.branch.actions" 
                        ng-click='cellTemplateScope.click(action.method)' 
                        icon='{{action.icon}}'
                        title='{{action.label}}'
                        
                        >
                        </nsj-action>
                        </nsj-actions>
                    </div>
                    `,
            },
        ];
    }

    /**
     * @description Criação de novo nó
     * @param identificador 
     * @param entity 
     * @param pai 
     */
    obterNovoNode(identificador, entity, pai) {
        const icon = this.obterIcone();
        const entityId = this.obterIdEntity(entity);
        const noNovo = angular.copy(this.montarNoTree(identificador, entity, entityId, pai, '', icon));
        
        return noNovo;
    }

    /**
     * Carregamento do ícone
     * @param identificador
     */
    obterIcone(){
        return 'fas fa-minus';
    }

    /**
     * @param entity 
     */
    obterIdEntity(entity){
        return entity.id;
    }

    montarNoTree = function (tipo, entity, identificador, pai = null, hashAuxiliar = '', iconLeaf) {
       
        const cod = identificador;
        const no = {
          _id_: cod,
          _parentId_: pai,
          _info_: null,
          children: [],
          tipo: tipo,
          icons: { iconLeaf },
          nomeNo: entity.servicosProdutos ? entity.servicosProdutos : null, 
          cobertura: entity.cobertura ? entity.cobertura : null,
          //orcamentoPrevisto: entity.orcamentoPrevisto ? entity.orcamentoPrevisto : null,
          orcamentoReal: entity.orcamentoReal ? entity.orcamentoReal : 0,
          desconto: entity.desconto ? entity.desconto : 0,
          acrescimo: entity.acrescimo ? entity.acrescimo : 0,
          obj: entity
        }

        return no;
    }

    reloadScope() {
        this.$scope.$applyAsync();
        const treeFormated = this.tree.getArvore(this.treetest);
        this.lstPropostasItens = treeFormated;
       
    }

    //Preencher node da propostaitem com suas familias e funções
    async preenchePropostasItens() {
        let myList = [];

        for (let i = 0; i < this.entity.listagemOrcamentos.length; i++) {
            const orcamentoProvisorio = this.entity.listagemOrcamentos[i];

            //DEFINE A SOMA DOS ITENS
            let somaItensFamilia: number = 0
            let editDisablePropostaitem = true;

            let nodeOrcamento = {};
            let valorOrcamentoReal: number = 0;
            if(orcamentoProvisorio.execucaodeservico == true) {
                //node execucao
                //OBTEM O NODE DE ORÇAMENTO DO PROPOSTAITEM
                nodeOrcamento = this.obterNodeExecucaoServico(orcamentoProvisorio);

            } else if(orcamentoProvisorio.propostaitemfamilia == null && orcamentoProvisorio.propostaitemfuncao == null) {
                //node cabeça
                //OBTEM O NODE DE ORÇAMENTO DA EXECUCAO DE SERVIÇO
                nodeOrcamento = this.obterNodePropostaitem(orcamentoProvisorio);
                // nodeOrcamento.orcamentoReal = nodeOrcamento.valor;
                nodeOrcamento['valorOrcamentoRealDefinido'] = true
                nodeOrcamento['editDisable'] = editDisablePropostaitem;
                // if(nodeOrcamento.possuifilho==true){
                //     nodeOrcamento['valorOrcamentoRealDisabled'] = true //SE TEM FILHO, BLOQUEIA O VALOR TOTAL
                // } else {
                //     nodeOrcamento['valorOrcamentoRealDisabled'] = false //SE NÃO, HABILITA O VALOR TOTAL
                // }
            } else if(orcamentoProvisorio.propostaitemfamilia != null && orcamentoProvisorio.propostaitemfuncao == null) {
                //node familia
                nodeOrcamento = this.obterNodePropostaitemFamilias(orcamentoProvisorio)

            } else if(orcamentoProvisorio.propostaitemfamilia == null && orcamentoProvisorio.propostaitemfuncao != null) {
                //node funcao
                nodeOrcamento = this.obterNodePropostaitemFuncoes(orcamentoProvisorio);
            }

            //DA UM PUSH
            myList.push(nodeOrcamento);
        }


        this.lstPropostasItens = myList;

        for (const node of this.lstPropostasItens) {
            const pai = node.pai ? node.pai : null ;
            const noNovo = this.obterNovoNode(node.tipo, node, pai)
            
            //Só adiciono aos somadores os valores das propostaitens, pois seus valores já são a soma dos seus filhos
            if(noNovo.tipo === 'propostaitem'){
                this.somaValor += parseFloat(noNovo.orcamentoReal)
                this.somaDesconto += parseFloat(noNovo.desconto)
                this.somaAcrescimo += parseFloat(noNovo.acrescimo)
            }    
            
            // passo minha lista `treeTest` apenas fazer push de itens da proposta;
            this.treetest.push(noNovo);

        }

        //Calculando o valor final, levando em consideração os acréscimos e descontos
        this.valorFinal = this.somaValor + this.somaAcrescimo - this.somaDesconto

        this.busy = false;
        this.reloadScope();





        // for (let i = 0; i < this.entity.length; i++) {
        //     const propostaItensFuncoes: any = await this.carregaPropostasitensFuncoes(this.entity[i].propostaitem);
        //     const propostaItensFamilias: any = await this.carregaPropostasitensFamilias(this.entity[i].propostaitem);
        //     this.entity[i].propostasitensfamilias = propostaItensFamilias;
        //     this.entity[i].propostasitensfuncoes = propostaItensFuncoes;

        //     let somaItensFamilia: number = 0
        //     const propostaItensOrcamentos: any = await this.carregaOrcamento(this.entity[i].propostaitem);

        //     //localizando orcamento do propostaitem
        //     const orcamentoPropostaItem = propostaItensOrcamentos.find(element => {
        //         if(
        //             element.propostaitemfamilia.propostaitemfamilia == null &&
        //             element.propostaitemfuncao.propostaitemfuncao == null &&
        //             // this.entity[i].servicoorcamento.orcamento != element.orcamento
        //             element.execucaodeservico !== true
        //         ){ return true; }
        //     });

        //     //localizando orcamento da execucao de servico
        //     const orcamentoExecucaoServico = propostaItensOrcamentos.find(element => {
        //         if(
        //             element.propostaitemfamilia.propostaitemfamilia == null &&
        //             element.propostaitemfuncao.propostaitemfuncao == null &&
        //             // this.entity[i].servicoorcamento.orcamento == element.orcamento
        //             element.execucaodeservico === true
        //         ){ return true; }
        //     });

        //     //Criando nó da proposta item
        //     myList.push(this.obterNodePropostaitem(this.entity[i], orcamentoPropostaItem));
        //     myList.push(this.obterNodeExecucaoServico(this.entity[i], orcamentoExecucaoServico));

        //     //Para cada propostaitemfuncao, busco seu orçamento e crio seu nó
        //     let somaItensFuncaoOrcamento: number = 0;
        //     propostaItensFuncoes.forEach(funcao => {
        //         const orcamentoFuncao = propostaItensOrcamentos.find(element => {
        //             if(
        //                 element.propostaitemfamilia.propostaitemfamilia == null &&
        //                 element.propostaitemfuncao.propostaitemfuncao == funcao.propostaitemfuncao
        //             ){ return true; }
        //         });
        //         myList.push(this.obterNodePropostaitemFuncoes(funcao, orcamentoFuncao));
        //         if(orcamentoFuncao !== undefined){
        //             somaItensFuncaoOrcamento += parseFloat(orcamentoFuncao.valor);
        //         }
        //     });

        //     //Para cada propostaitemfamilia, busco seu orçamento e crio seu nó
        //     let somaItensFamiliaOrcamento: number = 0;
        //     propostaItensFamilias.forEach(familia => {
        //         const orcamentoFamilia = propostaItensOrcamentos.find(element => {
        //             if(
        //                 element.propostaitemfuncao.propostaitemfuncao == null &&
        //                 element.propostaitemfamilia.propostaitemfamilia == familia.propostaitemfamilia
        //             ){ return true; }
        //         });
        //         myList.push(this.obterNodePropostaitemFamilias(familia, orcamentoFamilia));
        //         if(orcamentoFamilia !== undefined){
        //             somaItensFamiliaOrcamento += parseFloat(orcamentoFamilia.valor);
        //         }
        //     });

        //     let valorOrcamentoReal: number = 0;
        //     if(orcamentoPropostaItem !== undefined){
        //         let valorExecucao:number = orcamentoExecucaoServico.valor !== undefined ? parseFloat(orcamentoExecucaoServico.valor) : 0;
        //         // valorOrcamentoReal = somaItensFamiliaOrcamento + somaItensFuncaoOrcamento + valorExecucao;
        //         valorOrcamentoReal = orcamentoPropostaItem.valor;
        //     } else {
        //         //percorro cada propostaitemfamilia, somando o valor de cada uma
        //         propostaItensFamilias.forEach(itemfamilia => {
        //             somaItensFamilia += parseFloat(itemfamilia.valor)
        //         });
        //         //Somando o valor das propostasitensfamilias com o item de faturamento da propostaitem
        //         valorOrcamentoReal = somaItensFamilia + parseFloat(this.entity[i].itemdefaturamentovalor)
        //     }
            

        //     //Modifico o valor do orçamento real na lista
        //     myList.forEach(element => {
        //         if(element.id === this.entity[i].propostaitem){
        //             element.orcamentoReal = valorOrcamentoReal
        //             element['valorOrcamentoRealDefinido'] = true

        //             if(element.possuifilho==true){
        //                 element['valorOrcamentoRealDisabled'] = true
        //             } else {
        //                 element['valorOrcamentoRealDisabled'] = false
        //             }

        //         } 
        //     });

        // }

        // this.lstPropostasItens = myList;

        // for (const node of this.lstPropostasItens) {
        //     const pai = node.pai ? node.pai : null ;
        //     const noNovo = this.obterNovoNode(node.tipo, node, pai)
            
        //     //Só adiciono aos somadores os valores das propostaitens, pois seus valores já são a soma dos seus filhos
        //     if(noNovo.tipo === 'propostaitem'){
        //         this.somaValor += parseFloat(noNovo.orcamentoReal)
        //         this.somaDesconto += parseFloat(noNovo.desconto)
        //         this.somaAcrescimo += parseFloat(noNovo.acrescimo)
        //     }    
            
        //     // passo minha lista `treeTest` apenas fazer push de itens da proposta;
        //     this.treetest.push(noNovo);

        // }

        // //Calculando o valor final, levando em consideração os acréscimos e descontos
        // this.valorFinal = this.somaValor + this.somaAcrescimo - this.somaDesconto

        // this.busy = false;
        // this.reloadScope();

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

    carregaOrcamento(propostaitem){
        let constructors = {};

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_orcamentos_index', angular.extend({}, constructors, { 'offset': '', 'propostaitem': propostaitem }), true, true),
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    obterNodePropostaitem (orcamento) {
        let valorOrcamentoRealDefinido = false;
        let valorOrcamentoRealDisabled = true;
        let idOrcamento = null;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorOriginal = null;
        let descontoOriginal = null;
        let acrescimoOriginal = null;
        let possuiFilho = false;

        // if(propostaitem.propostasitensfuncoes.propostaitemfuncao !== null || 
        //     propostaitem.propostasitensfamilias.propostaitemfamilia !== null){
            possuiFilho = true;
        // }

        //verificar se tem orçamento para carregar os dados:

        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = orcamento.valor;
            desconto = orcamento.desconto;
            acrescimo = orcamento.acrescimo;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        }

        //se é propostaitem, produto == false.
        //se é propostaitem, pai == null
        //se é propostaitem, level == 1
        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitem',
            "id": orcamento.propostaitem,
            "level": 1,
            "possuifilho": possuiFilho, //definido buscando antes os propostasitens familia e produto.
            "pai": null,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": orcamento.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        }
        return newItem;
    }

    obterNodeExecucaoServico (orcamento) {
        let valorOrcamentoRealDefinido: boolean = true;
        let valorOrcamentoRealDisabled: boolean = false;
        let idOrcamento = null;
        let orcamentoReal: number = orcamento.itemdefaturamentovalor;
        let desconto = 0;
        let acrescimo = 0;
        let valorOriginal = null;
        let descontoOriginal = null;
        let acrescimoOriginal = null;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = orcamento.valor;
            desconto = orcamento.desconto;
            acrescimo = orcamento.acrescimo;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        }

        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'itemdefaturamento',
            "id": 'itemdefaturamento' + orcamento.propostaitem, //id tem o prefixo 'itemdefaturamento' concatenado com o id do seu pai
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e funcao.
            "pai": orcamento.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": 'Execução do Serviço',
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        };
        return newItem;
    }

    obterNodePropostaitemFuncoes (orcamento) {
        //defaults
        let valorOrcamentoRealDefinido = false;
        let valorOrcamentoRealDisabled = true;
        let idOrcamento = null;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorOriginal = null;
        let descontoOriginal = null;
        let acrescimoOriginal = null;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = orcamento.valor;
            desconto = orcamento.desconto;
            acrescimo = orcamento.acrescimo;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        }

        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitemfuncao',
            "id": orcamento.propostaitemfuncao,
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e produto.
            "pai": orcamento.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": orcamento.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        };
        return newItem;
    }

    obterNodePropostaitemFamilias (orcamento) {
        let valorOrcamentoRealDefinido = false;
        let valorOrcamentoRealDisabled = true;
        let idOrcamento;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorOriginal = null;
        let descontoOriginal = null;
        let acrescimoOriginal = null;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = orcamento.valor;
            desconto = orcamento.desconto;
            acrescimo = orcamento.acrescimo;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        } else if (orcamento.valor!=null || orcamento.valor!=undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = null;
            orcamentoReal = orcamento.valor;
            desconto = 0;
            acrescimo = 0;
        }
        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitemfamilia',
            "id": orcamento.propostaitemfamilia,
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e funcao.
            "pai": orcamento.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": orcamento.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": true,
            "children": [],
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        };
        return newItem;
    }

    

    submit() {
        this.submitted = true;
        if (this.form.$valid) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    
    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmFichafinanceiraFormService {
    static $inject = ['CrmPropostasitens', '$uibModal', '$http', 'nsjRouting', 'Tree'];

    constructor(
        public entityService: any,
        public $uibModal: any,
        public $http: any,
        public nsjRouting: any,
        public tree : Tree) {}

    open(fornecedorId: any, atc: any, proposta: any) {

            return this.$uibModal.open({
                template: require('../../Crm/Atcs/ficha-financeira-modal.html'),
                controller: 'CrmFichafinanceiraFormModalController',
                controllerAs: 'crm_fchfnncr_frm_dflt_cntrllr',
                windowClass: 'modal-lg-wrapper',
                resolve: {

                    entity: async () => {

                        //Construtor da proposta item
                        this.entityService.constructors['negocio'] = atc;
                        this.entityService.constructors['proposta'] = proposta;

                        if (fornecedorId && atc) {
                            let listagemOrcamentos = await this.$http({
                                method: 'GET',
                                url: this.nsjRouting.generate(
                                    'crm_orcamentos_listagem_orcamento_fichafinanceira',
                                    angular.extend({}, {'atc': atc, 'fornecedor': fornecedorId}, { 'offset': ''}), true),
                            });
                            return {'listagemOrcamentos': listagemOrcamentos.data};
                        }
                    },

                    constructors: () => {
                        return fornecedorId;
                    }
                }
            });
    }

}

type treeExpand = {
    field: string;
    displayName: string;
    sortable: boolean;
    filterable: boolean;
    cellTemplate?: string;
};
