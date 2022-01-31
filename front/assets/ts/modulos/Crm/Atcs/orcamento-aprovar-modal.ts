import angular = require('angular');
import { Tree } from '../../Commons/tree';
import { CrmOrcamentosReprovarSubModalService } from './orcamento-reprovar-submodal';
import { CrmOrcamentosRenegociarSubModalService } from './orcamento-renegociar-submodal';


export class CrmOrcamentosAprovarModalController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        'Tree',
        '$scope',
        '$filter',
        'nsjRouting',
        '$http',
        'CrmOrcamentosReprovarSubModalService',
        'CrmOrcamentosRenegociarSubModalService',
        '$rootScope'
    ];

    public action: string;
    public form: any;
    public submitted: boolean = false;

    public expanding_property: treeExpand;
    public col_defs: any;
    public treetest = [];

    public lstOrcamentosAprovar: any;
    public locaisOrcamentosAprovarInfo: any;
    public busyOrcamentosAprovar: boolean = false;
    novaTree: any[];

    public orcamentoRealTotal: number=0;
    public descontoTotal: number=0;
    public acrescimoTotal: number=0;
    public orcamentoTotal: number=0;

    public renegociarOrcamentos: boolean=false;

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public tree : Tree,
        public $scope: any,
        public $filter:any,
        public nsjRouting: any,
        public $http: any,
        public CrmOrcamentosReprovarSubModalService: CrmOrcamentosReprovarSubModalService,
        public CrmOrcamentosRenegociarSubModalService: CrmOrcamentosRenegociarSubModalService,
        public $rootScope: any
        ) {
    }

    isBusy(){
        return this.busyOrcamentosAprovar;
    }

    $onInit() {

        this.initColDef();
        this.getOrcamentosAprovar();

        this.getOrcamentosAprovarInfo();
        // this.recalcularDescontoTotal();
        // this.recalcularAcrescimoTotal(); 
        // this.recalcularOrcamentoTotal();
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
                field: ' ',
                displayName: ' ',
                cellTemplate: `
                <input ng-if="row.branch['renegociarOrcamento']==true && (row.branch.level==1 || row.branch.level==2)" type='checkbox' name="possuiseguradora" ng-model="row.branch['adicionarMotivo']" ng-change="cellTemplateScope.change(row)"/>
                `,
                cellTemplateScope: {
                    change: (row: any) => {
                        if(row.branch.adicionarMotivo==true){
                            this.abrirModalRenegociar(row);
                        } else {
                            row.branch.obj.motivo=null;
                        }
                    },
                }
            },
            {
                field: 'servicosProdutos',
                displayName: 'Serviços e Produtos',
            },
            {
                field: 'cobertura',
                displayName: 'Cobertura',
            },
            // {
            //     field: 'orcamentoPrevisto',
            //     displayName: 'Orçamento Previsto',
            //     cellTemplate: `
            //         <span ng-class="{treefontbold: row.branch.obj.produto==true, treefontcrossed: row.branch.obj.orcamentoPrevisto!=row.branch.obj.orcamentoReal}" ng-bind="(row.branch.orcamentoPrevisto | currency : 'R$ ' : 2) || '-'"></span>`
            // },
            {
                field: 'obj.orcamentoReal',
                displayName: 'Orçamento real',
                cellTemplate: `
                    <span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.obj.orcamentoReal | currency : 'R$ ' : 2) || '-'"></span>`,
            },
            {
                field: 'obj.desconto',
                displayName: 'Desconto',
                cellTemplate: `
                    <strong>- </strong><span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.obj.desconto | currency : 'R$ ' : 2) || '-'"></span>`,
                
            },
            {
                field: 'obj.acrescimo',
                displayName: 'Acréscimo',
                cellTemplate: `
                    <strong>+ </strong><span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.obj.acrescimo | currency : 'R$ ' : 2) || '-'"></span>`,           
            },

            {
                field: null,
                cellTemplate: `
                    <div class='table-btns-actions' ng-class="row.branch.actions.length==0 ? 'naopossuiacoes' : ''">
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
                cellTemplateScope: {
                    click: (acao: any) => {
                        this.traduzMetodo.call(this, acao);
                    },
                }
            },
        ];
    }

    parseValor(valor){
       const valorParseado =  parseFloat(String(valor).replace(/[^0-9,]/g, '').replace('.', '').replace(',', '.'));
       return valorParseado;
    }

    removerNo(id: string,hashIdentificador: string) {
        this.treetest = this.treetest.filter(dado => dado._id_ !== `${hashIdentificador}${id}`);
        }
    /**
     * @description Criação de novo nó e suas ações
     * @param identificador 
     * @param entity 
     * @param pai 
     * @param index 
     */
    obterNovoNode(identificador, entity, pai, index = null) {
        const icon = this.obterIcone(identificador);
        const entityId = this.obterIdEntity(identificador, entity);
        const actions = this.obterAcoesDoNode(identificador, entity, index);
        const noNovo = angular.copy(this.montarNoTree(identificador, entity, entityId, pai, '', actions, icon));
        return noNovo;
    }

    montarNoTree = function (tipo, entity, identificador, pai = null, hashAuxiliar = '', actions = [], iconLeaf) {
       
        const cod =   identificador ;
        const no = {
          _id_: cod,
          _parentId_: pai,
          _info_: null,
          children: [],
          tipo: tipo,
          actions: actions,
          icons: { iconLeaf },

          servicosProdutos: entity.servicosProdutos ? entity.servicosProdutos : null, 
          cobertura: entity.cobertura ? entity.cobertura : null,
          orcamentoPrevisto: entity.orcamentoPrevisto ? entity.orcamentoPrevisto : null,

          obj: entity
        }

        return no;
      }      

    /**
     * Verificando se o identificador é de um capítulo, item, proposta item função ou proposta item familia
     * @param identificador 
     * @param entity 
     */
    obterIdEntity(identificador, entity){
        switch (identificador) {
            case 'orcamentopreencher':
                return entity.id;
        }
    }

    /**
     * Carregamento do ícone, dependendo do seu tipo
     * @param identificador
     */
    obterIcone(identificador){
        switch (identificador){
            case 'orcamentopreencher':
                return 'fas fa-minus';
                
        }
    }
    /**
     * @description Constrói as ações do node
     */
    obterAcoesDoNode(identificador, entity, index = null){
        let acoes = [];

        switch (identificador) {
            case 'orcamentopreencher':
                acoes = [
                    // descomentar ícones ao implementar funções
                    // { 'label': 'Acionar Prestador', 'permission': false, 'icon': 'fas fa-bullhorn', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste('${entity.encarregado}','${entity.acionamento}',"",'${index}')` },
                    // { 'label': 'Trocar Prestador', 'permission': false, 'icon': 'fas fa-exchange-alt', 'size': 'xs', 'color': 'danger', 'method': `this.crmFichaFinanceiraTeste('${entity.encarregado}','${entity.acionamento}','${index}')` },
                    // { 'label': 'Cancelar Acionamento', 'permission': false, 'icon': 'fas fa-times', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Analisar Orçamento', 'permission': false, 'icon': 'fas fa-gavel', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Consultar itens do Pedido', 'permission': false, 'icon': 'fas fa-list', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Contactar Prestadora', 'permission': false, 'icon': 'fas fa-comment', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Histórico de Orçamentos', 'permission': false, 'icon': 'fas fa-history', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },

                ];
                return acoes;
        }
    }


    /*
    * Recalcula o valor do pai do respectivo agrupador
    */
    recalcularTotalColuna(coluna:any){
        let valorCalculado = 0;
        this.treetest.forEach(element => {
                if(element.obj.pai == coluna.obj.id){      
                    valorCalculado+=element.obj.orcamentoReal ? parseFloat(element.obj.orcamentoReal) : 0;
                }
        });
        return valorCalculado;
    }

    /*
    * Recalcula o somatório total do orcamento real
    */
    recalcularOrcamentoRealTotal(){
        this.orcamentoRealTotal=0;

        this.treetest.forEach(element => {
            if(element.obj.tipo == "propostaitem" && element.obj.valorOrcamentoRealDefinido==true && element.obj.orcamentoReal !== null && element.obj.orcamentoReal !== undefined){
                this.orcamentoRealTotal+=parseFloat(element.obj.orcamentoReal);
            }
        });
        this.recalcularOrcamentoTotal();
    }

    /*
    * Recalcula o somatório total do desconto 
    */
    recalcularDescontoTotal(){
        this.descontoTotal=0;

        this.treetest.forEach(element => {
            if(element.obj.tipo == "propostaitem" && element.obj.desconto !== null && element.obj.desconto !== undefined){
                this.descontoTotal+=parseFloat(element.obj.desconto);
            }
        });
        this.recalcularOrcamentoTotal();
    }

    /*
    * Recalcula o somatório total do acrescimo 
    */
   recalcularAcrescimoTotal(){
        this.acrescimoTotal=0;

        this.treetest.forEach(element => {
            if(element.obj.tipo == "propostaitem" && element.obj.acrescimo !== null && element.obj.acrescimo !== undefined){
                this.acrescimoTotal+=parseFloat(element.obj.acrescimo);
            }
        });
        this.recalcularOrcamentoTotal();
    } 
    
    /*
    * Recalcula o orcamento total
    */
    recalcularOrcamentoTotal(){
        this.orcamentoTotal = this.orcamentoRealTotal - this.descontoTotal + this.acrescimoTotal;
    } 

  
    /**
     * Carrega fichas financeiras
     * */
    async getOrcamentosAprovar() {

        this.busyOrcamentosAprovar = true;

        let myList = [];
        for (let i = 0; i < this.entity.length; i++) {
            const propostaitem = this.entity[i];
            const propostaItensFuncoes: any = await this.carregaPropostasitensFuncoes(propostaitem.propostaitem);
            const propostaItensFamilias: any = await this.carregaPropostasitensFamilias(propostaitem.propostaitem);
            propostaitem.propostasitensfamilias = propostaItensFamilias;
            propostaitem.propostasitensfuncoes = propostaItensFuncoes;
            let somaItensFamilia: number = 0

            const propostaItensOrcamentos: any = await this.carregaOrcamento(propostaitem);

            //localizando orcamento do propostaitem
            const orcamentoPropostaItem = propostaItensOrcamentos.find(element => {
                if(
                    element.propostaitemfamilia.propostaitemfamilia == null &&
                    element.propostaitemfuncao.propostaitemfuncao == null &&
                    // propostaitem.servicoorcamento.orcamento != element.orcamento
                    element.execucaodeservico !== true
                ){ return true; }
            });

            //localizando orcamento da execucao de servico
            const orcamentoExecucaoServico = propostaItensOrcamentos.find(element => {
                if(
                    element.propostaitemfamilia.propostaitemfamilia == null &&
                    element.propostaitemfuncao.propostaitemfuncao == null &&
                    // propostaitem.servicoorcamento.orcamento == element.orcamento
                    element.execucaodeservico === true
                ){ return true; }
            });

            myList.push(this.obterNodePropostaitem(propostaitem, orcamentoPropostaItem));
            myList.push(this.obterNodeExecucaoServico(propostaitem, orcamentoExecucaoServico));

            //processa orcamentos das propostasitensfuncoes
            let somaItensFuncaoOrcamento: number = 0;
            propostaItensFuncoes.forEach(funcao => {
                const orcamentoFuncao = propostaItensOrcamentos.find(element => {
                    if(
                        element.propostaitemfamilia.propostaitemfamilia == null &&
                        element.propostaitemfuncao.propostaitemfuncao == funcao.propostaitemfuncao
                    ){ return true; }
                });
                myList.push(this.obterNodePropostaitemFuncoes(funcao, orcamentoFuncao));
                if(orcamentoFuncao !== undefined){
                    somaItensFuncaoOrcamento += parseFloat(orcamentoFuncao.valor);
                }
            });

            //processa orcamentos das propostasitensfamilias
            let somaItensFamiliaOrcamento: number = 0;
            propostaItensFamilias.forEach(familia => {
                const orcamentoFamilia = propostaItensOrcamentos.find(element => {
                    if(
                        element.propostaitemfuncao.propostaitemfuncao == null &&
                        element.propostaitemfamilia.propostaitemfamilia == familia.propostaitemfamilia
                    ){ return true; }
                });
                myList.push(this.obterNodePropostaitemFamilias(familia, orcamentoFamilia));
                if(orcamentoFamilia !== undefined){
                    somaItensFamiliaOrcamento += parseFloat(orcamentoFamilia.valor);
                }
            });

            let valorOrcamentoReal: number = 0;
            if(orcamentoPropostaItem !== undefined){
                let valorExecucao:number = orcamentoExecucaoServico.valor !== undefined ? parseFloat(orcamentoExecucaoServico.valor) : 0;
                // valorOrcamentoReal = somaItensFamiliaOrcamento + somaItensFuncaoOrcamento + valorExecucao;
                valorOrcamentoReal = orcamentoPropostaItem.valor;
            } else {
                //percorro cada propostaitemfamilia, somando o valor de cada uma
                propostaItensFamilias.forEach(itemfamilia => {
                    somaItensFamilia += parseFloat(itemfamilia.valor)
                });
                //Somando o valor das propostasitensfamilias com o item de faturamento da propostaitem
                valorOrcamentoReal = somaItensFamilia + parseFloat(propostaitem.itemdefaturamentovalor)
            }
            

            //Modifico o valor do orçamento real na lista
            myList.forEach(element => {
                if(element.id === propostaitem.propostaitem){
                    element.orcamentoReal = valorOrcamentoReal
                    element['valorOrcamentoRealDefinido'] = true

                    if(element.possuifilho==true){
                        element['valorOrcamentoRealDisabled'] = true
                    } else {
                        element['valorOrcamentoRealDisabled'] = false
                    }

                } 
            });

            this.recalcularOrcamentoRealTotal();
        }

        this.lstOrcamentosAprovar = myList;

        for (const orcamento of this.lstOrcamentosAprovar) {
            /** 
             * @todo condicional para remoção: não ser pai  
             * */
            const pai = orcamento.pai? orcamento.pai : null ;
            const noNovo = this.obterNovoNode('orcamentopreencher', orcamento, pai)

            // passo minha lista `treeTest` apenas fazer push de itens da proposta;
            // await this.obterPropostasItens(ficha, propostaindex)
            this.treetest.push(noNovo);
        }

        ////// sempre que terminar a requisição para preencher a tree, deve-se calcular as somas totais:
        this.recalcularOrcamentoRealTotal();
        this.recalcularDescontoTotal();
        this.recalcularAcrescimoTotal(); 
        this.recalcularOrcamentoTotal();

        this.reloadScope();
        this.busyOrcamentosAprovar = false;

    }
    
    obterNodePropostaitemFuncoes (funcao, orcamento) {
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
            // valorOrcamentoRealDefinido = true;
            // valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;

            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        }

        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitemfuncao',
            "id": funcao.propostaitemfuncao,
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e produto.
            "pai": funcao.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": funcao.funcao.descricao,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "motivo": null,
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        };
        return newItem;
    }

    obterNodePropostaitemFamilias (familia, orcamento) {
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
            // valorOrcamentoRealDefinido = true;
            // valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        } else if (familia.valor!=null || familia.valor!=undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = null;
            orcamentoReal = familia.valor;
            desconto = 0;
            acrescimo = 0;
        }
        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitemfamilia',
            "id": familia.propostaitemfamilia,
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e funcao.
            "pai": familia.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": familia.familia.descricao,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": true,
            "children": [],
            "motivo": null,
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        };
        return newItem;
    }

    obterNodeExecucaoServico (propostaitem, orcamento) {
        let valorOrcamentoRealDefinido: boolean = false;
        let valorOrcamentoRealDisabled: boolean = true;
        let idOrcamento = null;
        let orcamentoReal: number = propostaitem.itemdefaturamentovalor;
        let desconto = 0;
        let acrescimo = 0;
        let valorOriginal = null;
        let descontoOriginal = null;
        let acrescimoOriginal = null;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            // valorOrcamentoRealDefinido = true;
            // valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
        }

        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'itemdefaturamento',
            "id": 'itemdefaturamento' + propostaitem.propostaitem, //id tem o prefixo 'itemdefaturamento' concatenado com o id do seu pai
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e funcao.
            "pai": propostaitem.propostaitem,
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
            "motivo": null,
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        };
        return newItem;
    }

    obterNodePropostaitem (propostaitem, orcamento) {
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

        if(propostaitem.propostasitensfuncoes.propostaitemfuncao !== null || 
            propostaitem.propostasitensfamilias.propostaitemfamilia !== null){
            possuiFilho = true;
        }

        //verificar se tem orçamento para carregar os dados:

        if (orcamento !== undefined) { //mudar
            // valorOrcamentoRealDefinido = true;
            // valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
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
            "id": propostaitem.propostaitem,
            "level": 1,
            "possuifilho": possuiFilho, //definido buscando antes os propostasitens familia e produto.
            "pai": null,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": propostaitem.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "motivo": null,
            "original": {
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
            }
        }
        return newItem;
    }

    reloadScope() {
        this.$scope.$applyAsync();
        const treeFormated = this.tree.getArvore(this.treetest);
        this.lstOrcamentosAprovar = treeFormated;
       
    }

    traduzMetodo(metodo) {
        return eval(metodo);
    };
    /**@todo
     * implementar funcionalidade quando tiver dados reais
     */
    /** Informações de local de velorio e sepultamento **/
    getOrcamentosAprovarInfo(){
        this.locaisOrcamentosAprovarInfo = null 

        // this.locaisOrcamentosAprovarInfo = {
        //     "id": 1,
        //     "localSepultamento": {
        //         "pais": null,
        //         "estado": null,
        //         "cidade": null,
        //         "cemiterio": null,
        //         "sepultamento": null,
        //     },
        //     "localVelorio1": {
        //         "pais": null,
        //         "estado": null,
        //         "cidade": null,
        //         "capela": null,
        //         "previsaoInicio": null,
        //         "previsaoFim": null,
        //     },
        //     "localVelorio2": {
        //         "pais": null,
        //         "estado": null,
        //         "cidade": null,
        //         "capela": null,
        //         "previsaoInicio": null,
        //         "previsaoFim": null,
        //     },
        // };

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
    carregaPropostasitensFamilias( propostaitem) {
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
        // constructors['negocio'] = parameters.negocio;
        // constructors['proposta'] = parameters.proposta;

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_orcamentos_index', angular.extend({}, constructors, { 'offset': '', 'propostaitem': propostaitem.propostaitem }), true, true),
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    processarOrcamentos () {
        let orcamentos = {"orcamentos": []};
        this.treetest.forEach(element => {
            let motivo = element.obj.motivo == null ? null : element.obj.motivo;
            let item = {
                "orcamento": element.obj.orcamento,
                "negocio": this.entity.negocio,
                "proposta": this.entity.proposta,
                "motivo": motivo
            };
            orcamentos.orcamentos.push(item);
        });
        return orcamentos;
    }

    async aprovarOrcamento(){
        this.busyOrcamentosAprovar = true;
        let orcamentos = this.processarOrcamentos();
        //for é sincrono, foreach é asincrono
        let erros = false;
        for (let index = 0; index < orcamentos.orcamentos.length; index++) {
            const orcamento = orcamentos.orcamentos[index];

            let orcamentosInsertResponse = await this.$http({
                method: 'POST',
                data: angular.copy(orcamento),
                url: this.nsjRouting.generate('crm_orcamentos_aprovar', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
            }).catch(error => {
                erros = true;
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
                this.busyOrcamentosAprovar = false;
            });
            this.$rootScope.$broadcast('crm_orcamentos_aprovar', orcamento);
        }
        
        if(!erros){
            this.fecharComSucesso();
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao aprovar orçamento.'
            });
        } else{
            this.close();
        }

    }

    toggleRenegociarOrcamento(){
        if(this.renegociarOrcamentos==false){       
            this.treetest.forEach(element => {
                element["renegociarOrcamento"]=true;
            });
            this.renegociarOrcamentos=true;
        }else{   
            this.treetest.forEach(element => {
                element["renegociarOrcamento"]=false;
            });
            this.renegociarOrcamentos=false;
        }
        this.reloadScope();
    }


    async sendRenegociarOrcamento(){
        this.busyOrcamentosAprovar = true;
        let orcamentos = this.processarOrcamentos();
        //for é sincrono, foreach é asincrono
        let erros = false;
        for (let index = 0; index < orcamentos.orcamentos.length; index++) {
            const orcamento = orcamentos.orcamentos[index];
            if(orcamento.motivo === null) continue;

            let orcamentosInsertResponse = await this.$http({
                method: 'POST',
                data: angular.copy(orcamento),
                url: this.nsjRouting.generate('crm_orcamentos_renegociar', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
            }).catch(error => {
                erros = true;
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
                this.busyOrcamentosAprovar = false;
            });
        }
        
        if(!erros){
            this.fecharComSucesso();
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao enviar orçamento para renegociação.'
            });
        }
        else{
            this.close();
        }
    }

    async reprovarOrcamento(motivo = null){
        this.busyOrcamentosAprovar = true;
        let orcamentos = this.processarOrcamentos();
        //for é sincrono, foreach é asincrono
        let erros = false;
        for (let index = 0; index < orcamentos.orcamentos.length; index++) {
            const orcamento = orcamentos.orcamentos[index];

            let orcamentosInsertResponse = await this.$http({
                method: 'POST',
                data: angular.copy(orcamento),
                url: this.nsjRouting.generate('crm_orcamentos_reprovar', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
            })
            .catch(error => {
                erros = true;
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
                this.busyOrcamentosAprovar = false;
            });
        }
       
        if(!erros){
            this.fecharComSucesso();
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao reprovar orçamento.'
            });
        } else{
            this.close();
        }
    }

    preencheMotivoRenegociacaoNaTree(row:any,motivo:string){
        this.treetest.forEach(element => {
            if(element._id_ == row.branch._id_){
                element.obj.motivo = motivo == null ? '' : motivo;
            }
        });
    }

    zeraCheckbox(row:any){
        this.treetest.forEach(element => {
            if(element._id_ == row.branch._id_){
                element.obj.motivo = null;
                element.obj.motivo = null;
                element.adicionarMotivo = false;
            }
        });
    }

    abrirModalRenegociar(row:any) {
        if(row.branch['adicionarMotivo']==true){
            let modal = this.CrmOrcamentosRenegociarSubModalService.open({'row':row}, '', ''); 
            modal.result.then((motivo: any) => {
                this.preencheMotivoRenegociacaoNaTree(row,motivo.motivo);
                
            })
            .catch((error: any) => {
                if (error === 'backdrop click' || error === 'fechar'){
                    this.zeraCheckbox(row);
                }
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    // this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
        }
    }

    preencheMotivoNaTree(motivo){
        this.treetest.forEach(element => {
            element.obj.motivo = motivo == null ? '' : motivo;
        });
    }

    abrirModalReprovar() {
        let modal = this.CrmOrcamentosReprovarSubModalService.open('', '', '');
        modal.result.then((motivo: any) => {
            this.preencheMotivoNaTree(motivo.motivo);
            this.reprovarOrcamento();
        })
        .catch((error: any) => {
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                // this.busy = false;
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }
    
    fecharComSucesso(){
        this.$uibModalInstance.close(true);
    }

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


export class CrmOrcamentosAprovarModalService {
    static $inject = ['CrmPropostascapitulos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    /* Incluindo o pai como parametro opcional*/
    open(parameters: any, subentity: any, paiId: any) {
            return this.$uibModal.open({
                template: require('../../Crm/Atcs/orcamento-aprovar-modal.html'),
                controller: 'CrmOrcamentosAprovarModalController',
                controllerAs: 'crm_orcmnts_aprvr_mdl_cntrllr',
                windowClass: 'modal-lg-wrapper',
                resolve: {
                entity: async () => {
                    if (parameters.proposta && parameters.atc && parameters.fornecedor) {
                        //    let entity = this.entityService.get(parameters.proposta, parameters.propostacapitulo);
                        //    entity.pai = paiId;

                        this.entityService.constructor = {};
                        this.entityService.constructors['negocio'] = parameters.atc;
                        this.entityService.constructors['proposta'] = parameters.proposta;

                        let { data: entity } = await this.$http({
                            method: 'GET',
                            url: this.nsjRouting.generate(
                                'crm_propostasitens_index',
                                angular.extend({}, this.entityService.constructors, { 'offset': '', 'fornecedor': parameters.fornecedor }), true, true),
                        });
                        entity.atc = parameters.atc;
                        entity.proposta = parameters.proposta;
                        entity.fornecedor = parameters.fornecedor;
                        return entity;
                    } else {
                        let entity=null;
                    return entity;
                    }
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }

}
