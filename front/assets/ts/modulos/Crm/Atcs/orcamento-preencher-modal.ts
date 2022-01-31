import angular = require('angular');
import { Tree } from '../../Commons/tree';
import { CrmOrcamentosObservacoesSubModalService } from './orcamento-observacoes-submodal';

export class CrmOrcamentosPreencherModalController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        'Tree',
        '$scope',
        '$filter', 
        'nsjRouting',
        '$http',
        '$rootScope',
        'CrmOrcamentosObservacoesSubModalService',
        'CrmPropostasitensfamiliasFormService',
        'CrmPropostasitensfamilias',
        'CrmPropostasitensfuncoesFormService',
        'CrmPropostasitensfuncoes'
    ];

    private _propostaitem: any;
    private _index: number;

    public action: string;
    public form: any;
    public submitted: boolean = false;

    public expanding_property: treeExpand;
    public col_defs: any;
    public treetest = [];

    public lstOrcamentosPreencher: any;
    public locaisOrcamentosPreencherInfo: any;
    public busyOrcamentosPreencher: boolean = false;
    novaTree: any[];

    public orcamentoRealTotal: number=0;
    public descontoTotal: number=0;
    public acrescimoTotal: number=0;
    public orcamentoTotal: number=0;
    //Variável que faz o controle da exibição do alert na tela
    public msgErroDesconto: boolean =  false;
    public busy: boolean = true;

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public tree : Tree,
        public $scope: any,
        public $filter:any,
        public nsjRouting: any,
        public $http: any,
        public $rootScope: any,
        public CrmOrcamentosObservacoesSubModalService: any,
        public CrmPropostasitensfamiliasFormService: any,
        public CrmPropostasitensfamilias: any,
        public CrmPropostasitensfuncoesFormService: any,
        public CrmPropostasitensfuncoes: any
    ) {
    }

    isBusy(){
        return this.busyOrcamentosPreencher;
    }
    $onInit() {

        this.initColDef();
        this.getOrcamentosPreencher();
        this.ouvirSalvarProduto();
        this.ouvirSalvarProfissional();

        this.getOrcamentosPreencherInfo();
        this.recalcularDescontoTotal();
        this.recalcularAcrescimoTotal();
        this.recalcularOrcamentoTotal();
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
                field: 'servicosProdutos',
                displayName: 'Serviços e Produtos',
                cellTemplate: `
                    <div ng-class="{treeitemexcluido: row.branch.obj.excluido==true}"> 
                        <nsj-tooltip ng-if="cellTemplateScope.mostraIconeEditado(row.branch)" tooltip-position="bottom">
                            <p><strong>O ITEM FOI ALTERADO!</strong></p>
                            <p>Analise as alterações para saber o motivo, as mudanças feitas e o prestador responsável.</p>
                        </nsj-tooltip>
                        <span ng-if="cellTemplateScope.mostraIconeEditado(row.branch)"><i class="fas fa-user-edit tree-icon-mod-status"></i></span>


                        <!-- ******** -->
                        <!-- VERIFICAR QUE CONDIÇÕES DEVEM SER ADICIONADAS AO TOOLTIP E ÍCONE DE ADICIONADO -->
                        <!-- ****** -->
                        <nsj-tooltip ng-if="(row.branch.obj.adicionado==true) && (row.branch.obj.excluido!=true)" tooltip-position="bottom">
                            <p><strong>O ITEM FOI ADICIONADO!</strong></p>
                            <p>Analise as alterações para saber o motivo, as mudanças feitas e o prestador responsável.</p>
                        </nsj-tooltip>
                        <span ng-if="(row.branch.obj.adicionado==true) && (row.branch.obj.excluido!=true)"><i class="fas fa-plus tree-icon-mod-status"></i></span>
                        <!-- ******** -->
                        <!-- VERIFICAR QUE CONDIÇÕES DEVEM SER ADICIONADAS AO TOOLTIP E ÍCONE DE ADICIONADO -->
                        <!-- ****** -->

                        <span ng-bind="row.branch.obj.servicosProdutos || '-'"></span>
                    </div>
                    `,
                cellTemplateScope: {
                    mostraIconeEditado: (branch:any)=>{
                        if((branch.obj.editado==true) && (branch.obj.adicionado!=true) && (branch.obj.excluido!=true)) { 
                            return true;
                        } else {
                            let keepGoing = true;

                            if(branch.children.length > 0){
                                branch.children.forEach(child => {
                                    if(keepGoing){
                                        if((child.obj.editado!= undefined && child.obj.editado == true) || (child.obj.adicionado!= undefined && child.obj.adicionado == true) || (child.obj.excluido!= undefined && child.obj.excluido == true)){
                                            keepGoing = false;
                                        }
                                    }   
                                })
                            }

                            if (keepGoing){
                                return false;
                            } else{
                                return true;
                            }
                        }
                    },   
                }
            },
            // {
            //     field: 'cobertura',
            //     displayName: 'Cobertura',
            //     cellTemplate: `
            //         <span ng-class="{treeitemexcluido: row.branch.obj.excluido==true}" ng-bind="row.branch.obj.cobertura || '-'"></span>
            //         `,
            // },
            // {
            //     field: 'orcamentoPrevisto',
            //     displayName: 'Orçamento Previsto',
            //     ////// ng-if="row.branch.produto" => identificador se é primeiro ou segundo nivel (recebem tag <strong>) 
            //     ////// ou terceiro nivel (não recebem tag <strong>)
            //     cellTemplate: `
            //         <span ng-class="{treefontbold: row.branch.obj.produto==true}" ng-bind="(row.branch.orcamentoPrevisto | currency : 'R$ ' : 2) || '-'"></span>`
            // },
            {
                field: 'obj.orcamentoReal',
                displayName: 'Valor Receber',
                cellTemplate: `
                    <div ng-class="{treeitemexcluidoinput: row.branch.obj.excluido==true}">
                        <input 
                        ng-change="cellTemplateScope.salvaValorreceber(row.branch.obj)"
                        ng-blur="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj,$event)"
                        ng-keyup="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj, $event)"
                        class="form-control tree-input text-right" 
                        ng-model="row.branch.obj.valorreceber"
                        ng-disabled="row.branch.obj.loading == true || row.branch.obj.valorOrcamentoRealDisabled==true || row.branch.obj.faturar == false || row.branch.obj.editDisable == true || row.branch.obj.excluido==true"
                        ui-money-mask>
                       
                        <input
                        ng-if="!row.branch.obj.valorOrcamentoRealDefinido"
                        ng-disabled="!row.branch.valorOrcamentoRealDefinido || row.branch.obj.excluido==true"
                        class="form-control tree-input text-right"
                        ng-value="'Valor indefinido'">

                    </div>`,
                cellTemplateScope: {
                    // obterValor: (valor)=>{
                    //     let valorNumber = valor;
                    //     if(valorNumber || valorNumber === 0) {
                    //         return valorNumber;
                    //     }
                    //     return 'Digite o valor';
                    // },   
                    // indefinidoOuDefinido: (linhaId: any) => {
                    //     this.indefinidoOuDefinido(linhaId);
                    // },
                    salvaValorreceber: async (linha: any) => {
                        linha.valorReceberDefinido = true;
                        this.salvarValorDigitado(linha,'valorreceber');
                    },
                    salvarAlteracoesOrcamento: async (linha, $event) => {
                        if( $event.keyCode === undefined || $event.keyCode == 13) {
                            await this.salvarAlteracoesOrcamento(linha);
                        }
                    },
                    // validaObrigatorio : (campo) =>{
                    //    return  campo.level!=1 && (campo.obj.orcamentoReal==null || campo.obj.orcamentoReal=='');
                    // }
                }
            },
            {
                field: 'obj.orcamentoReal',
                displayName: 'Orçamento real',
                ////// ng-if="ow.branch.valorOrcamentoRealDefinido" => identificador se o orçamento está selecionado
                ////// como definido ou indefinido
                ////// alterar função indefinidoOuDefinido() com o identificador correto.

                /*

                    Será preciso colocar uma configuração na área de atendimento para ser possível discriminar ou não os valores dos
                    filhos da proposta item (modificar seus valores manualmente no preenchimento do orçamento)

                    Trecho comentado para não ser possível editar o valor da propostaitem (o valor será sempre a soma dos filhos) e sempre ser possível
                    o preenchimento dos valores dos filhos da propostaitem
                    
                    - A exibição do botão está comentada, para possibilitar a não discriminação de valores.
                    - O valor da propostaitem não é editável, ela é sempre a soma dos seus filhos
                    - O valor dos filhos da propostaitem (propostaitemfamilia, propostaitemfuncao e execução do serviço) sempre terão de ser preenchidos e
                      sempre podem ter seus valores alterados.

                    Trecho iria dentro da div abaixo, antes do primeiro input. Trecho a seguir é o responsável pela exibição dos botões
                    <nsj-button ng-if="row.branch.obj.valorOrcamentoRealDefinido" ng-click="cellTemplateScope.indefinidoOuDefinido(row.branch.obj.id)" color="default" size="sm" class="tree-input-select-button"><i class="fas fa-eye"></i> &nbsp; &nbsp;</nsj-button> 
                    <nsj-button ng-if="!row.branch.obj.valorOrcamentoRealDefinido" ng-click="cellTemplateScope.indefinidoOuDefinido(row.branch.obj.id)" color="default" size="sm" class="tree-input-select-button"><i class="fas fa-eye-slash"></i> <i class="fas fa-caret-down"></i></nsj-button>
                
                */

                cellTemplate: `
                    <div ng-class="{treeitemexcluidoinput: row.branch.obj.excluido==true}">

                        <input 
                        ng-class="{treecampoobrigatorio: cellTemplateScope.validaObrigatorio(row.branch), treefontbold: row.branch.obj.produto==true}" 
                        ng-if="row.branch.obj.valorOrcamentoRealDefinido" 
                        ng-change="cellTemplateScope.salvaOrcamentoReal(row.branch.obj,orcamentoReal)"
                        ng-blur="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj,$event)"
                        ng-keyup="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj, $event)"
                        class="form-control tree-input text-right" 
                        ng-model="row.branch.obj.orcamentoReal"                         
                        ng-disabled="row.branch.obj.loading == true || row.branch.obj.valorOrcamentoRealDisabled==true || row.branch.obj.faturar == false || row.branch.obj.editDisable == true || row.branch.obj.excluido==true"
                        ui-money-mask>
                       
                        <input
                        ng-if="!row.branch.obj.valorOrcamentoRealDefinido"
                        ng-disabled="!row.branch.valorOrcamentoRealDefinido || row.branch.obj.excluido==true"
                        class="form-control tree-input text-right"
                        ng-value="'Valor indefinido'">

                    </div>`,
                cellTemplateScope: {
                    obterValor: (valor)=>{
                        let valorNumber = valor;
                        if(valorNumber || valorNumber === 0) {
                            return valorNumber;
                        }
                        return 'Digite o valor';
                    },   
                    indefinidoOuDefinido: (linhaId: any) => {
                        this.indefinidoOuDefinido(linhaId);
                    },
                    salvaOrcamentoReal: (linha: any) => {
                        this.salvarValorDigitado(linha,'orcamentoReal');
                        if(linha.valorReceberDefinido === false){
                            //atualizar no valor a receber
                            linha.valorreceber = linha.orcamentoReal;
                            this.salvarValorDigitado(linha,'valorreceber');
                        }
                    },
                    validaObrigatorio : (campo) =>{
                       return  campo.level!=1 && (campo.obj.orcamentoReal==null || campo.obj.orcamentoReal=='');
                    },
                    salvarAlteracoesOrcamento: async (linha, $event) => {
                        if( $event.keyCode === undefined || $event.keyCode == 13) {
                            await this.salvarAlteracoesOrcamento(linha);
                        }
                    },
                }
            },
            {
                field: 'obj.desconto',
                displayName: 'Desconto',
                cellTemplate: `
                    <div ng-class="{treeitemexcluidoinput: row.branch.obj.excluido==true}">
                        <strong>- </strong>

                        <input
                        max = "row.branch.obj.orcamentoReal"
                        ng-class="{treefontbold: row.branch.level<3}"
                        class="form-control tree-input text-right"
                        ng-disabled="row.branch.obj.loading == true || row.branch.obj.faturar == false || row.branch.obj.editDisable == true || row.branch.obj.excluido==true"
                        ng-change="cellTemplateScope.salvaDesconto(row.branch.obj,desconto)"
                        ng-blur="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj,$event)"
                        ng-keyup="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj, $event)"
                        ng-model="row.branch.obj.desconto"   
                        ui-money-mask    
                        > 
                        <span ng-if="row.branch.obj.msgErroDesconto == true" ng-hide="row.branch.obj.msgErroDesconto == false">
                        <i width: 100% class="fas fa-exclamation-circle red"/>
                        <nsj-tooltip tooltip-position="top"><p><strong>Desconto não pode ser maior que o orçamento! </strong></p></nsj-tooltip>
                        </span>
                        <!--button ng-if="row.branch.obj.adicionarNovo==false" ng-click="cellTemplateScope.edit({'texto':row.branch.nome},row.branch.obj,this.$modelValue)" type="button" class="action-btn-tree-table"><i class="fas fa-caret-square-right adicionacapitulohover"></i></button>
                        <button ng-if="row.branch.obj.adicionarNovo==true" ng-click="cellTemplateScope.add({'texto':row.branch.nome},row.branch.obj.pai,this.$modelValue)" type="button" class="action-btn-tree-table"><i class="fas fa-caret-square-right"></i></button>

                        <button ng-if="row.branch.obj.adicionarNovo==true" ng-click="cellTemplateScope.remove()" class="action-btn-tree-table" type="button"><i class="fas fa-times"></i></button-->
                    </div>`,
                cellTemplateScope: {
                    salvaDesconto: (linha: any) => {
                        //Controle para não permitir que o desconto seja maior que o valor do orçamento
                        //Com a propriedade max no input de desconto, quando o desconto é maior que o orçamento, ele chega como undefined
                        if (linha.desconto == undefined){
                            this.msgErroDesconto =  true;
                            linha.msgErroDesconto = true;
                            //Zerando o desconto em tela
                            linha.desconto = 0
                            this.salvarValorDigitado(linha,'desconto');
                            return true;
                        } else {
                            this.msgErroDesconto = false
                            linha.msgErroDesconto = false;
                            this.salvarValorDigitado(linha,'desconto');
                        }
                        
                        

                    },
                    retornaMsg: (linha: any) => {

                        //Controle para não permitir que o desconto seja maior que o valor do orçamento
                        //Com a propriedade max no input de desconto, quando o desconto é maior que o orçamento, ele chega como undefined
                        if (this.msgErroDesconto == true) {
                            return true;
                        }
                    },
                    salvarAlteracoesOrcamento: async (linha, $event) => {
                        if( $event.keyCode === undefined || $event.keyCode == 13) {
                            await this.salvarAlteracoesOrcamento(linha);
                        }
                    },
                    // obterDesconto: (desconto)=>{
                    //     let descontoNumber = (desconto !== null && desconto !== undefined ) && this.parseValor(desconto);
                    //     if(descontoNumber || descontoNumber === 0) {
                    //         return this.$filter('currency')(descontoNumber,'R$',2);
                    //     }                        
                    //     return 'Digite o valor';                        
                    // // }
                    // ,
                    //     ng-value="cellTemplateScope.obterDesconto(row.branch.obj.desconto)"
                }
            },
            {
                field: 'obj.acrescimo',
                displayName: 'Acréscimo',
                cellTemplate: `
                    <div ng-class="{treeitemexcluidoinput: row.branch.obj.excluido==true}">
                        <strong>+ </strong>

                        <input
                        ng-class="{treefontbold: row.branch.level<3}"
                        class="form-control tree-input text-right"
                        ng-disabled="row.branch.obj.loading == true || row.branch.obj.faturar == false || row.branch.obj.excluido==true || row.branch.obj.editDisable == true"
                        ng-change="cellTemplateScope.salvaAcrescimo(row.branch.obj,acrescimo)"
                        ng-blur="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj,$event)"
                        ng-keyup="cellTemplateScope.salvarAlteracoesOrcamento(row.branch.obj, $event)"
                        ng-model="row.branch.obj.acrescimo"
                        ui-money-mask
                        >

                        <!--button ng-if="row.branch.obj.adicionarNovo==false" ng-click="cellTemplateScope.edit({'texto':row.branch.nome},row.branch.obj,this.$modelValue)" type="button" class="action-btn-tree-table"><i class="fas fa-caret-square-right adicionacapitulohover"></i></button>
                        <button ng-if="row.branch.obj.adicionarNovo==true" ng-click="cellTemplateScope.add({'texto':row.branch.nome},row.branch.obj.pai,this.$modelValue)" type="button" class="action-btn-tree-table"><i class="fas fa-caret-square-right"></i></button>

                        <button ng-if="row.branch.obj.adicionarNovo==true" ng-click="cellTemplateScope.remove()" class="action-btn-tree-table" type="button"><i class="fas fa-times"></i></button-->
                    </div>`,
                cellTemplateScope: {
                    salvaAcrescimo: (linha: any) => {
                        this.salvarValorDigitado(linha,'acrescimo');       
                    },
                    salvarAlteracoesOrcamento: async (linha, $event) => {
                        if( $event.keyCode === undefined || $event.keyCode == 13) {
                            await this.salvarAlteracoesOrcamento(linha);
                        }
                    },
                    // obterAcrescimo: (acrescimo)=>{
                    //     let acrescimoNumber = (acrescimo !== null && acrescimo !== undefined ) && this.parseValor(acrescimo);
                    //     if(acrescimoNumber || acrescimoNumber === 0) {
                    //         return this.$filter('currency')(acrescimoNumber,'R$',2);
                    //     }                        
                    //     return 'Digite o valor';                        
                    // }
                    //ng-value="cellTemplateScope.obterAcrescimo(row.branch.obj.acrescimo)"
                }
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
                        ng-if='cellTemplateScope.desabilitarExcluido(action,row.branch.obj) && cellTemplateScope.desabilitarComFilhos(action,row.branch)'
                        >
                        </nsj-action>
                        </nsj-actions>
                    </div>
                    `,
                cellTemplateScope: {
                    click: (acao: any) => {
                        this.traduzMetodo.call(this, acao);
                    },
                    desabilitarExcluido: (acao: any, obj: any) => {
                        if(obj.excluido!= undefined && obj.excluido==true){
                            if(acao.id != 5){
                                return false;
                            } else{
                                return true;
                            }
                        } else {
                            if(acao.id == 5){
                                return false;
                            } else{
                                return true;
                            }
                            
                        }
                    },
                    desabilitarComFilhos: (acao: any, obj: any) => {
                        if(obj.children.length > 0){
                            if(acao.id == 2){
                                return false;
                            } else{
                                return true;
                            }
                        } else {
                            return true;
                            
                        }
                    },
                }
            },
            {
                field: 'faturar',
                displayName: 'Faturar*',
                cellTemplate: `
                <div class="custom-checkbox checkbox" ng-if="row.branch.obj.tipo != 'propostaitem'">
                    <label class="control-label">
                        <input 
                            type='checkbox' class="" 
                            id="faturar" name="faturar" 
                            ng-model="row.branch.obj.faturar" 
                            ng-if="row.branch.obj.loading != true"
                            ng-click="cellTemplateScope.checkFaturar(row)"/> 
                        <span class="checkmark"></span>
                    </label>
                </div>`,
                cellTemplateScope: {
                    checkFaturar: async (linha: any) => {
                       this.mudaEstadoCheckFaturar(linha);
                       await this.salvarAlteracoesOrcamento(linha.branch.obj);
                    },
                }
            }
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

        if(identificador == 'itemfamilias'){
            entity = this.obterNodePropostaitemFamiliasNovo(entity);
        }

        else if(identificador == 'itemfuncoes'){
            entity = this.obterNodePropostaitemFuncoesNovo(entity);
        }

        let noNovo = angular.copy(this.montarNoTree(identificador, entity, entityId, pai, '', actions, icon));
        //adicionar novas propriedades para cada nó da tree
        //  if(noNovo.tipo  === 'propostaitem'){
        //      noNovo.status =  obterStatus(entity);
        //  }
        return noNovo;
    }

    //Função que cria um novo nó de produto
    obterNodePropostaitemFamiliasNovo (familia) {
        let valorOrcamentoRealDefinido = true;
        let valorOrcamentoRealDisabled = false;
        let valorReceberDefinido = false;
        let idOrcamento;
        let valorreceber = parseFloat(familia.valor);
        let orcamentoReal = parseFloat(familia.valor);
        let desconto = 0;
        let acrescimo = 0;
        let valorreceberOriginal = valorreceber;
        let valorOriginal = orcamentoReal;
        let descontoOriginal = desconto;
        let acrescimoOriginal = acrescimo;
        let faturar: boolean;
        let faturarOriginal: boolean;
        let editDisable = false;
        let tipoAtualizacao = null;
        let tipoAtualizacaoOriginal = null;
        let excluido: boolean;

        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitemfamilia',
            "id": familia.propostaitemfamilia,
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens familia e funcao.
            "pai": familia.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "valorReceberDefinido": valorReceberDefinido,
            "editDisable": editDisable,
            "orcamentoReal": orcamentoReal,
            "valorreceber": valorreceber,
            "servicosProdutos": familia.familia.descricao,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": true,
            "children": [],
            "original": {
                "valorreceber": valorreceberOriginal,
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
                "faturar": faturarOriginal,
                "tipoatualizacao": tipoAtualizacaoOriginal
            },
            "faturar": faturar,
            "tipoatualizacao": tipoAtualizacao,
            "excluido": tipoAtualizacao === 0 ? true : false,
            "adicionado": tipoAtualizacao === 1 ? true : false
        };
        return newItem;
    }

    //Funçao que cria um novo nó de profissional
    obterNodePropostaitemFuncoesNovo (funcao) {
        let valorOrcamentoRealDefinido = true;
        let valorOrcamentoRealDisabled = false;
        let valorReceberDefinido = false;
        let idOrcamento;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorOriginal = orcamentoReal;
        let valorreceberOriginal = orcamentoReal;
        let descontoOriginal = desconto;
        let acrescimoOriginal = acrescimo;
        let faturar: boolean;
        let faturarOriginal: boolean;
        let editDisable = false;
        let tipoAtualizacao = null;
        let tipoAtualizacaoOriginal = null;
        let excluido: boolean;


        let newItem = {
            "orcamento": idOrcamento,
            "tipo": 'propostaitemfuncao',
            "id": funcao.propostaitemfuncao,
            "level": 2,
            "possuifilho": false, //definido buscando antes os propostasitens funcao e funcao.
            "pai": funcao.propostaitem,
            "valorOrcamentoRealDefinido": valorOrcamentoRealDefinido,
            "valorOrcamentoRealDisabled": valorOrcamentoRealDisabled,
            "valorReceberDefinido": valorReceberDefinido,
            "editDisable": editDisable,
            "orcamentoReal": 0,//parseFloat(funcao.valor),
            "valorreceber": 0,//parseFloat(funcao.valor),
            "servicosProdutos": funcao.funcao.descricao,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "original": {
                "valorreceber": valorreceberOriginal,
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
                "faturar": faturarOriginal,
                "tipoatualizacao": tipoAtualizacaoOriginal
            },
            "faturar": faturar,
            "tipoatualizacao": tipoAtualizacao,
            "excluido": tipoAtualizacao === 0 ? true : false,
            "adicionado": tipoAtualizacao === 1 ? true : false
        };
        return newItem;
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
        //   orcamentoReal: entity.orcamentoReal ? entity.orcamentoReal : null,
        //   desconto: entity.desconto ? entity.desconto : null,
        //   acrescimo: entity.acrescimo ? entity.acrescimo : null,
        //   valorOrcamentoRealDefinido: entity.valorOrcamentoRealDefinido,

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
        // switch (identificador) {
            // case 'orcamentopreencher':
            if (identificador == 'itemfamilias'){
                return entity.propostaitemfamilia;
            }
            else if(identificador == 'itemfuncoes'){
                return entity.propostaitemfuncao;
            }
            else{
                return entity.id;
            }
        // }
    // }
    }

    /**
     * Carregamento do ícone, dependendo do seu tipo
     * @param identificador
     */
    obterIcone(identificador){
        // switch (identificador){
        //     case 'orcamentopreencher':
                return 'fas fa-minus';
                
        // }
    }
    /**
     * @description Constrói as ações do node
     */
    obterAcoesDoNode(identificador, entity, index = null){
        /*********
         * 
         * Legenda Ações:
         * 1 = editar // 2 = excluir // 3 = adicionar profissional // 4 = adicionar produto // 5 = restaurar excluído // 6 = visualizar
         * 
         */
        let acoes = [];
        // switch (identificador) {
        switch (entity.tipo) {
            case 'orcamentopreencher':
                acoes = [
                    //Botão editar removido pois no momento de preencher orçamento não é possível editar itens. Comentado pois futuramente pode ser que precise
                    // { 'id': 1, 'label': 'Editar', 'icon': 'fas fa-edit', 'size': 'xs', 'color': 'primary', 'method': `this.orcamentoEditar('${entity.id}')` },
                    { 'id': 2, 'label': 'Excluir', 'icon': 'fas fa-trash-alt', 'size': 'xs', 'color': 'danger', 'method': `this.orcamentoExcluir('${entity.id}')` },
                    { 'id': 3, 'label': 'Adicionar Profissional', 'icon': 'fas fa-user', 'size': 'xs', 'color': 'primary', 'method': `this.orcamentoAdicionarProfissional('${entity.id}')` },
                    { 'id': 4, 'label': 'Adicionar Produto', 'icon': 'fas fa-shopping-basket', 'size': 'xs', 'color': 'primary', 'method': `this.orcamentoAdicionarProduto('${entity.id}')` },
                    { 'id': 5, 'label': 'Restaurar Produto Excluído', 'icon': 'fas fa-trash-restore-alt', 'size': 'xs', 'color': 'danger', 'method': `this.orcamentoCancelarExcluir('${entity.id}')` }
                    // { 'id':6, 'label': 'Visualizar Item', 'permissionuser': 'crm_propostasitens_get', 'permission': true, 'icon': 'fas fa-eye', 'size': 'xs', 'color': 'primary', 'method': `this.crmPropostasitensForm('${entity.proposta}', '${entity.propostaitem}', '${index}', '1')` }
                ];
                return acoes;
            break;
            case 'propostaitem':
                acoes = [
                    // descomentar ícones ao implementar funções
                    { 'id': 3, 'label': 'Adicionar Profissional', 'icon': 'fas fa-user', 'size': 'xs', 'color': 'primary', 'method': `this.orcamentoAdicionarProfissional(${null}, '${entity.id}', ${null}, '${index}')` },
                    { 'id': 4, 'label': 'Adicionar Produto', 'icon': 'fas fa-shopping-basket', 'size': 'xs', 'color': 'primary', 'method': `this.orcamentoAdicionarProduto(${null}, '${entity.id}', ${null}, '${index}')` },
                    // { 'label': 'Acionar Prestador', 'permission': false, 'icon': 'fas fa-bullhorn', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste('${entity.encarregado}','${entity.acionamento}',"",'${index}')` },
                    // { 'label': 'Trocar Prestador', 'permission': false, 'icon': 'fas fa-exchange-alt', 'size': 'xs', 'color': 'danger', 'method': `this.crmFichaFinanceiraTeste('${entity.encarregado}','${entity.acionamento}','${index}')` },
                    // { 'label': 'Cancelar Acionamento', 'permission': false, 'icon': 'fas fa-times', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Analisar Orçamento', 'permission': false, 'icon': 'fas fa-gavel', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Consultar itens do Pedido', 'permission': false, 'icon': 'fas fa-list', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Contactar Prestadora', 'permission': false, 'icon': 'fas fa-comment', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                    // { 'label': 'Histórico de Orçamentos', 'permission': false, 'icon': 'fas fa-history', 'size': 'xs', 'color': 'primary', 'method': `this.crmFichaFinanceiraTeste("",'${entity.encarregado}', '${entity.acionamento}','${index}')` },
                ];
                if(entity.observacaocomposicao !== null){
                    acoes.push({ 'label': 'Visualizar Observações', 'permissionuser': 'crm_propostasitens_get', 'permission': true, 'icon': 'fas fa-comment-dots', 'size': 'xs', 'color': 'primary', 'method': `this.modalObservacao('${entity.servicosProdutos}','${entity.observacaocomposicao}')`});
                }
                return acoes;
            break;

            default:
                acoes = [
                    //Botão editar removido pois no momento de preencher orçamento não é possível editar itens. Comentado pois futuramente pode ser que precise
                    // { 'id': 1, 'label': 'Editar', 'icon': 'fas fa-edit', 'size': 'xs', 'color': 'primary', 'method': `this.orcamentoEditar('${entity.id}')` },
                    { 'id': 2, 'label': 'Excluir', 'icon': 'fas fa-trash-alt', 'size': 'xs', 'color': 'danger', 'method': `this.orcamentoExcluir('${entity.id}')` },
                    { 'id': 5, 'label': 'Restaurar Produto Excluído', 'icon': 'fas fa-trash-restore-alt', 'size': 'xs', 'color': 'danger', 'method': `this.orcamentoCancelarExcluir('${entity.id}')` }
                ];
                return acoes;
        }
    }

    /**
     * Abre modal de observação.
     * @param nome 
     * @param obs 
     */
    modalObservacao(nome,obs) {
        let modal = this.CrmOrcamentosObservacoesSubModalService.open({'nome':nome,'obs':obs}, {});
        modal.result.then((item: any) => {
        })
        .catch((error: any) => {
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.busyOrcamentosPreencher = false;
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }

    orcamentoEditar(entityId:string){
        this.treetest.forEach(element => {
            if(element.obj.id == entityId){
                element.obj["editado"] = true;
            }
        });
        this.reloadScope();
    }

    async orcamentoExcluir(entityId:string){
        // this.busyOrcamentosPreencher = true;
        let idElementoPai: any = null;
        let orcamentoId: any;
        let obj = {};
        this.treetest.forEach(element => {
            if(element.obj.id == entityId){
                element.obj["excluido"] = true;
                element.obj["tipoatualizacao"] = 0;
                orcamentoId = element.obj.orcamento;
                idElementoPai = element.obj.pai;
                obj = element.obj;
            }
        });

        this.orcamentoAlterarTipoAtualizacao(orcamentoId, idElementoPai);
        this.salvarAlteracoesOrcamento(obj);
        this.reloadScope();
    }

    async orcamentoCancelarExcluir(entityId:string){
        // this.busyOrcamentosPreencher = true;
        let idElementoPai: any = null;
        let orcamentoId: any;
        let obj = {};
        this.treetest.forEach(element => {
            if(element.obj.id == entityId){
                element.obj["excluido"] = false;
                element.obj["tipoatualizacao"] = -1;
                orcamentoId = element.obj.orcamento;
                idElementoPai = element.obj.pai;
                obj = element.obj;
            }
        });
    
        this.orcamentoAlterarTipoAtualizacao(orcamentoId, idElementoPai);
        this.salvarAlteracoesOrcamento(obj);
        this.reloadScope();
    }

    //Modifica o tipoatualização na tabela
    async orcamentoAlterarTipoAtualizacao(orcamentoId: any, idElementoPai: any){
        // this.busyOrcamentosPreencher = true;
        // let orcamentos;
        // let orcamentoObj;

        //Após atualizar o orçamento na Tree, chamo esse método para pegar os objetos dos orçamentos já formatados para envio
        // orcamentos = this.processarOrcamentos().orcamentos;

        // //Buscando no array de orçamentos o orçamento que acabei de atualizar
        // for(let i = 0; i < orcamentos.length; i++){
        //     if (orcamentos[i].orcamento == orcamentoId){
        //         orcamentoObj = orcamentos[i];
        //         break;
        //     }
        // }

        //Mudando o tipoatualizacao no banco -- SOLUÇÃO NÃO VAI SER IMPLEMENTADA MAIS ASSIM
        // await this.$http({
        //     method: 'PUT',
        //     data: angular.copy(orcamentoObj),
        //     url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamentoObj.orcamento}, { 'offset': '', 'filter': '' }), true),
        // }).then((response: any) => {
        //     this.busyOrcamentosPreencher = false;
        // }).catch(error => {
        //     this.toaster.pop({
        //         type: 'error',
        //         title: error
        //     });
        //     this.busyOrcamentosPreencher = false;
        // });

        //Achando o nó pai e recalculando seu orçamento, desconto e acrescimo
        for(let i = 0; i < this.treetest.length; i++){

            if (this.treetest[i].obj.id == idElementoPai){

                this.atualizaValorPai(idElementoPai, 'orcamentoReal');
                this.recalcularOrcamentoRealTotal();

                this.atualizaValorPai(idElementoPai, 'desconto');
                this.recalcularDescontoTotal();
                
                this.atualizaValorPai(idElementoPai, 'acrescimo');
                this.recalcularAcrescimoTotal();

                this.atualizaValorPai(idElementoPai, 'valorreceber');

                break;
            }
        }
    }

    //Abre modal para inserção de um novo produto
    orcamentoAdicionarProduto(propostaitemfamilia, propostaitem, familia, index){
        let propostaItemFamiliaObj = {};
        if (propostaitemfamilia !== null) {
            propostaItemFamiliaObj = { propostaitemfamilia: propostaitemfamilia, propostaitem: propostaitem, familia: familia }
        } else {
            propostaItemFamiliaObj = {};
        }
        let modal = this.CrmPropostasitensfamiliasFormService.open(propostaItemFamiliaObj, {});

        modal.result.then((propostaitemfamilia: any) => {
                this.busy = true;
                this.CrmPropostasitensfamilias.constructors = { 'propostaitem': propostaitem };
                this.CrmPropostasitensfamilias.save(propostaitemfamilia);
                this._index = index;
                this._propostaitem = propostaitem;   
            })
                .catch((error: any) => {
                    if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                        this.busy = false;
                        this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                    }
                });

        // this.treetest.forEach(element => {
            // inserir propriedade "adicionado" = true ao elemento
            // adicionado que será um filho desde entityId.
            // para que seja exibido o ícone "+"
            //
            // element.obj["adicionado"] = true;
            //
        // });
        
    }

    //Após o produto ser inserido, carregar esse novo nó na tree de orçamentos
    ouvirSalvarProduto() {
        this.$scope.$on('crm_propostasitensfamilias_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const propostaitemfamilia = isNew ? response.data : entity;

            propostaitemfamilia.nome = propostaitemfamilia.nome || propostaitemfamilia.familia.nome;
            propostaitemfamilia.descricao = propostaitemfamilia.descricao || propostaitemfamilia.familia.descricao;

            const paiPropostaitem = propostaitemfamilia.propostaitem;
            const node = this.obterNovoNode('itemfamilias', propostaitemfamilia, paiPropostaitem, this._index);
            node.tipo = 'propostaitemfamilia';

            if (isNew) {
                node.obj.adicionado = true;
                node.obj.faturar = true;
                node.obj.tipoatualizacao = 1;
                this.treetest.push(node);
                this.salvarAlteracoesOrcamento(node.obj);
            } else {
                this.tree.editarNo(node, this.treetest);
            }

            this.atualizaValorPai(paiPropostaitem, 'orcamentoReal');
            this.atualizaValorPai(paiPropostaitem, 'valorreceber');
            this.recalcularOrcamentoRealTotal();
            this.busy = false;
            this.reloadScope();
        })
    }

    //Abre modal para inserção de um novo profissional
    orcamentoAdicionarProfissional(propostaitemfuncao, propostaitem, funcao, index){
        let propostaItemFuncaoObj = {};
        if (propostaitemfuncao !== null) {
            propostaItemFuncaoObj = { propostaitemfuncao: propostaitemfuncao, propostaitem: propostaitem, funcao: funcao }
        } else {
            propostaItemFuncaoObj = {};
        }
        let modal = this.CrmPropostasitensfuncoesFormService.open(propostaItemFuncaoObj, {});

        modal.result.then((propostaitemfuncao: any) => {
                this.busy = true;
                this.CrmPropostasitensfuncoes.constructors = { 'propostaitem': propostaitem };
                this.CrmPropostasitensfuncoes.save(propostaitemfuncao);
                this._index = index;
                this._propostaitem = propostaitem;   
            })
                .catch((error: any) => {
                    if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                        this.busy = false;
                        this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                    }
                });

        // this.treetest.forEach(element => {
            // inserir propriedade "adicionado" = true ao elemento
            // adicionado que será um filho desde entityId.
            // para que seja exibido o ícone "+"
            //
            // element.obj["adicionado"] = true;
            //
        // });
        
    }

    //Após o profissional ser inserido, carregar esse novo nó na tree de orçamentos
    ouvirSalvarProfissional() {
        this.$scope.$on('crm_propostasitensfuncoes_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const propostaitemfuncao = isNew ? response.data : entity;

            propostaitemfuncao.nome = propostaitemfuncao.nome || propostaitemfuncao.funcao.nome;
            propostaitemfuncao.descricao = propostaitemfuncao.descricao || propostaitemfuncao.funcao.descricao;

            const paiPropostaitem = propostaitemfuncao.propostaitem;
            const node = this.obterNovoNode('itemfuncoes', propostaitemfuncao, paiPropostaitem, this._index);
            node.tipo = 'propostaitemfuncao';

            if (isNew) {
                node.obj.adicionado = true;
                node.obj.faturar = true;
                node.obj.tipoatualizacao = 1;
                this.treetest.push(node);
                this.salvarAlteracoesOrcamento(node.obj);
            } else {
                this.tree.editarNo(node, this.treetest);
            }

            this.atualizaValorPai(paiPropostaitem, 'orcamentoReal');
            this.atualizaValorPai(paiPropostaitem, 'valorreceber');
            this.recalcularOrcamentoRealTotal();
            this.busy = false;
            this.reloadScope();
        })
    }


    /* 
    * Seta input para habilitado ou desabilitado de acordo com o identificador definido/indefinido
    */
    indefinidoOuDefinido(linhaId:any){
        let linhaModificada = null;
        this.treetest.forEach(element => {
            if(element.obj.id==linhaId){
                if (element.obj.valorOrcamentoRealDefinido){
                    element.obj.valorOrcamentoRealDefinido = false;
                    element.obj.valorOrcamentoRealDisabled = true;
                    element.obj.orcamentoReal = null;
                } else{
                    element.obj.valorOrcamentoRealDefinido = true;
                    element.obj.valorOrcamentoRealDisabled = false;
                }   
                linhaModificada=element;      
            }
        });  
        if (linhaModificada!=null){
            let elementoPai = null;
            this.treetest.forEach(element => {
                if(element.obj.pai == linhaModificada.obj.pai && element.obj.pai!=null){
                    element.obj.valorOrcamentoRealDefinido = linhaModificada.obj.valorOrcamentoRealDefinido;
                    element.obj.valorOrcamentoRealDisabled = linhaModificada.obj.valorOrcamentoRealDisabled;
                    element.obj.orcamentoReal = null;
                }
                if(element.obj.id==linhaModificada.obj.pai){
                    element.obj.valorOrcamentoRealDefinido = true;
                    if(linhaModificada.obj.valorOrcamentoRealDefinido==true){
                        element.obj.valorOrcamentoRealDisabled = true;
                    } else{
                        element.obj.valorOrcamentoRealDisabled = false;
                    }
                    elementoPai=element;
                }
                if(element.obj.pai == linhaModificada.obj.id){
                    if(linhaModificada.obj.valorOrcamentoRealDefinido==false && element.obj.valorOrcamentoRealDefinido==true){
                        element.obj.valorOrcamentoRealDefinido = linhaModificada.obj.valorOrcamentoRealDefinido;
                        element.obj.valorOrcamentoRealDisabled = linhaModificada.obj.valorOrcamentoRealDisabled;
                        element.obj.orcamentoReal = null;
                    }
                }
            });
                
            if(elementoPai!=null){
                elementoPai.obj.orcamentoReal = this.recalcularTotalColuna(elementoPai);
                this.trataElementoPaidoPai(linhaModificada,elementoPai); 
            }
            if(linhaModificada.level!=3){
            }
        }
        this.recalcularOrcamentoRealTotal();
        this.reloadScope();

    }

    /*
    * Recalcula o valor do pai do respectivo agrupador
    */
    recalcularTotalColuna(coluna:any){
        let valorCalculado = 0;
        this.treetest.forEach(element => {
                if(element.obj.pai == coluna.obj.id && element.obj.excluido != true){      
                    valorCalculado+=element.obj.orcamentoReal ? parseFloat(element.obj.orcamentoReal) : 0;
                }
        });
        return valorCalculado;
    }
    recalcularTotalColunaValorReceber(coluna:any){
        let valorCalculado = 0;
        this.treetest.forEach(element => {
            if(element.obj.pai == coluna.obj.id){
                valorCalculado+=element.obj.valorreceber ? parseFloat(element.obj.valorreceber) : 0;
            }
        });
        return valorCalculado;
    }

    /* Distribui o valor digitado na propostaitem entre os filhos */
    distribuirValorEntreFilhos(linhaId: any, valorADividir: number, campo: any){
        let contadorFilhos: number = 0;
        let valorDivididoFilhos: number = 0;
        let diferenca: number = 0;
        let somar: boolean = null;

        //Contando quantos filhos a propostaitem tem
        for(let i = 0; i < this.treetest.length; i++){
            if(this.treetest[i]._parentId_ == linhaId && this.treetest[i].obj.faturar == true){

                //Se o nó tem o orçamento real zero e estou digitando um desconto, não conto ele como filho, pois ele não receberá valor
                contadorFilhos += (campo == 'desconto' && this.treetest[i].obj.orcamentoReal == 0) ? 0 : 1;
            }                    
        }

        //Dividindo o valor digitado na propostaitem pela quantidade de filhos
        if(contadorFilhos != 0){
            valorDivididoFilhos = valorADividir / contadorFilhos
        }
        

        //Pegando apenas 2 casas decimais do valor dividido
        let valorDivididoDuasCasas = valorDivididoFilhos.toFixed(2)

        //Auxiliar para verificar se o valor dividido x numero de filhos é diferente do valor digitado no pai
        let valorComparacao = (parseFloat(valorDivididoDuasCasas) * contadorFilhos)

        //Zerando o contador de filhos para usa-lo para modificar o valor do primeiro filho, caso precise
        contadorFilhos = 0;

        //Compensar a diferença entre valores no primeiro filho (valor digitado no pai e somatório do valor dos filhos)
        if (valorADividir != valorComparacao){
            
            if(valorADividir > valorComparacao){
                diferenca = valorADividir - valorComparacao;
                somar = true;
            }
            else {
                diferenca = valorComparacao - valorADividir;
                somar = false;
            }

        }

        //Distribuindo o valor entre os filhos
        for(let i = 0; i < this.treetest.length; i++){

            if(this.treetest[i]._parentId_ == linhaId && this.treetest[i].obj.faturar == true){

                //Verificando se o campo é desconto e o orçamento é zero. Caso seja, não distribuo para ele
                if(!(this.treetest[i].obj.orcamentoReal == 0 && campo == 'desconto')){

                    //Se o contador for zero, então é o primeiro filho. Se somar for diferente de nulo, então há diferença entre os valores
                    if(contadorFilhos === 0 && somar != null && somar != undefined){
                        
                        if(somar == true){
                            this.treetest[i].obj[campo] = valorDivididoFilhos + diferenca;
                        }

                        else{
                            this.treetest[i].obj[campo] = valorDivididoFilhos - diferenca;
                        }

                        contadorFilhos++;

                    }

                    else{
                        this.treetest[i].obj[campo] = valorDivididoFilhos
                    }
                }
            }
        }

    }

    /*
    * Salva o valor digitado em seu respectivo campo na tree e recalcula o somatório total 
    */
    salvarValorDigitado(linha:any,campo:string){
        let valorNumber = linha[campo];
        let elementoPai=null;
        switch (campo) {
            case 'valorreceber':
                linha.valorreceber = valorNumber;
                if(linha.pai!=null){
                    for(let i=0; i<this.treetest.length;i++){
                        if(this.treetest[i].obj.id == linha.pai 
                            // && this.treetest[i].obj.valorOrcamentoRealDisabled==true
                        ){
                            this.treetest[i].obj.valorreceber = this.recalcularTotalColunaValorReceber(this.treetest[i]);
                            this.treetest[i].obj.valorReceberDefinido = linha.valorReceberDefinido;
                            elementoPai=this.treetest[i];
                        }
                    }
                }
                //Quando o check é desmarcado, o objeto "linha" vem dessa forma
                else if(linha.branch.obj.pai!=null){
                    for(let i=0; i<this.treetest.length;i++){
                        if(this.treetest[i].obj.id == linha.branch.obj.pai 
                            // && this.treetest[i].obj.valorOrcamentoRealDisabled==true
                        ){
                            this.treetest[i].obj.valorreceber = this.recalcularTotalColunaValorReceber(this.treetest[i]);
                            elementoPai=this.treetest[i];
                        }
                    }
                }
                // this.recalcularOrcamentoRealTotal();
                if(elementoPai!=null){
                    this.trataElementoPaidoPaiValorReceber(linha,elementoPai);
                }
                this.reloadScope();
                break;
            case 'orcamentoReal':
                linha.orcamentoReal = valorNumber;
                // let elementoPai=null;
                if(linha.pai!=null){
                    for(let i=0; i<this.treetest.length;i++){
                        if(this.treetest[i].obj.id == linha.pai && this.treetest[i].obj.valorOrcamentoRealDisabled==true){
                            this.treetest[i].obj.orcamentoReal = this.recalcularTotalColuna(this.treetest[i]);
                            elementoPai=this.treetest[i];
                        }
                    }
                }

                //Quando o check é desmarcado, o objeto "linha" vem dessa forma
                else if(linha.branch.obj.pai!=null){
                    for(let i=0; i<this.treetest.length;i++){
                        if(this.treetest[i].obj.id == linha.branch.obj.pai && this.treetest[i].obj.valorOrcamentoRealDisabled==true){
                            this.treetest[i].obj.orcamentoReal = this.recalcularTotalColuna(this.treetest[i]);
                            elementoPai=this.treetest[i];
                        }                    
                    }
                }

                this.recalcularOrcamentoRealTotal();
                if(elementoPai!=null){
                    this.trataElementoPaidoPai(linha,elementoPai);       
                }         
                this.reloadScope();
                break;
            case 'desconto':
                let descontoTotal:number = 0;
                let flagControle: boolean = false; //Controlar se já distribuiu a diferença do desconto com orçamento real para outro filho
                //percorro a treetest para ver quem são os irmãos do nó que estou modificando o desconto, para atualizar o valor do desconto no pai

                //Acumulando os descontos dos filhos da propostaitem pai do nó que estou digitando o desconto
                if (linha.pai != null){
                    for(let i = 0; i < this.treetest.length; i++){
                        if(this.treetest[i]._parentId_ == linha.pai){
                            descontoTotal += parseFloat(this.treetest[i].obj.desconto)
                        }                    
                    }

                    //Percorrendo a tree para achar o nó da propostaitem para atribuir ao valor do seu desconto, o somatório dos descontos dos filhos
                    for(let i = 0; i < this.treetest.length; i++){
                        if(this.treetest[i]._id_ == linha.pai){
                            this.treetest[i].obj.desconto = descontoTotal
                            break;
                        }                    
                    }

                }

                //Se for propostaitem, divido o valor do desconto entre seus filhos
                if(linha.tipo === 'propostaitem'){
                    let diferencaDesconto: number = 0;
                    let acumuladorDiferencaDesconto: number = 0;

                    this.distribuirValorEntreFilhos(linha.id, linha.desconto, 'desconto');

                    //Verificar se em algum dos filhos, o desconto ficou maior que o orçamento
                    for(let i = 0; i < this.treetest.length; i++){
                        if(this.treetest[i]._parentId_ == linha.id){

                            //Desconto maior que o orçamento
                            if(this.treetest[i].obj.orcamentoReal < this.treetest[i].obj.desconto){
                                
                                diferencaDesconto = this.treetest[i].obj.desconto - parseFloat(this.treetest[i].obj.orcamentoReal)

                                //Subtraindo a diferença do valor do desconto
                                this.treetest[i].obj.desconto -= parseFloat(diferencaDesconto.toFixed(2))

                                //Acumulando todos as diferenças
                                acumuladorDiferencaDesconto += parseFloat(diferencaDesconto.toFixed(2))

                            }
                        }
                    }

                    //Pegar a diferença do desconto, e somar a um filho que: orcamentoReal > desconto + diferencaDesconto
                    if (acumuladorDiferencaDesconto != 0){
                        let i = 0;
                        while(i < this.treetest.length && flagControle != true){
                            if(this.treetest[i]._parentId_ == linha.id){

                                //Orçamento deve maior que a soma do desconto atual + a diferenca do desconto
                                if(this.treetest[i].obj.orcamentoReal > (this.treetest[i].obj.desconto + acumuladorDiferencaDesconto)){

                                    //Formatando para usar apenas duas casas decimais para fazer o controle do valor
                                    this.treetest[i].obj.desconto += parseFloat(acumuladorDiferencaDesconto.toFixed(2))
                                    flagControle = true

                                }
                            }
                            i++;
                        }
                    }
                }    

                /*
                    Quando o valor é digitado no campo e quando o check é desmarcado, o objeto "linha" tem estruturas
                    diferentes, por isso é necessária essa verificação
                */

                //Digitando o valor no filho
                else if(linha.pai != null && linha.pai != undefined){
                    this.atualizaValorPai(linha.pai, 'desconto');
                }

                //Desmarcando o check
                else if(linha.branch.obj.pai != null && linha.branch.obj.pai != undefined){
                    this.atualizaValorPai(linha.branch.obj.pai, 'desconto')                    
                }
                
                // linha.desconto = this.$filter('currency')(valorNumber,'R$',2);
                this.recalcularDescontoTotal();
                break;
            case 'acrescimo':
                let acrescimoTotal:number = 0;
                //percorro a treetest para ver quem são os irmãos do nó que estou modificando o acrescimo, para atualizar o valor do acrescimo no pai

                //Acumulando os acrescimos dos filhos da propostaitem pai do nó que estou digitando o acrescimo
                if (linha.pai != null){
                    for(let i = 0; i < this.treetest.length; i++){
                        if(this.treetest[i]._parentId_ == linha.pai){
                            acrescimoTotal += parseFloat(this.treetest[i].obj.acrescimo)
                        }                    
                    }

                    //Percorrendo a tree para achar o nó da propostaitem para atribuir ao valor do seu acrescimo, o somatório dos acrescimos dos filhos
                    for(let i = 0; i < this.treetest.length; i++){
                        if(this.treetest[i]._id_ == linha.pai){
                            this.treetest[i].obj.acrescimo = acrescimoTotal
                            break;
                        }                    
                    }

                }

                //Se for propostaitem, divido o valor do acrescimo entre seus filhos
                if(linha.tipo === 'propostaitem'){

                    this.distribuirValorEntreFilhos(linha.id, linha.acrescimo, 'acrescimo');
                }

                /*
                    Quando o valor é digitado no campo e quando o check é desmarcado, o objeto "linha" tem estruturas
                    diferentes, por isso é necessária essa verificação
                */

                //Digitando o valor no filho
                else if(linha.pai != null && linha.pai != undefined){
                    this.atualizaValorPai(linha.pai, 'acrescimo');
                }

                //Desmarcando o check
                else if(linha.branch.obj.pai != null && linha.branch.obj.pai != undefined){
                    this.atualizaValorPai(linha.branch.obj.pai, 'acrescimo')
                }

                //linha.acrescimo = this.$filter('currency')(valorNumber,'R$',2);
                this.recalcularAcrescimoTotal();
                break;
        }
    }

    /* Atualizar o valor do pai baseado nos filhos */
    atualizaValorPai(pai: any, campo: string){
        let valorTotal: number = 0;
        let valorFormatado;

        //Acumulando os valores dos filhos da propostaitem pai do nó que estou manipulando. Só acumulo se ele não tiver excluído
        if (pai != null){
            for(let i = 0; i < this.treetest.length; i++){
                if(this.treetest[i]._parentId_ == pai && this.treetest[i].obj.excluido != true){
                    //Formatação necessária para manter 2 casas decimais e manter integridade do calculo
                    valorFormatado = parseFloat(this.treetest[i].obj[campo])
                    valorTotal += parseFloat(valorFormatado.toFixed(2))
                }                    
            }
        }

        //Percorrendo a tree para achar o nó da propostaitem para atribuir ao valor do seu campo, o somatório dos valores dos filhos
        for(let i = 0; i < this.treetest.length; i++){
            if(this.treetest[i]._id_ == pai){
                this.treetest[i].obj[campo] = parseFloat(valorTotal.toFixed(2))
                break;
            }                    
        }
    }

    /* Verifica se tem pelo menos 1 item faturado */
    verificaItemFaturado(arrayOrcamentos: any){
        let itemFaturado = false;

        for(let i=0; i < arrayOrcamentos.orcamentos.length; i++){

            if(arrayOrcamentos.orcamentos[i].faturar == true){
                itemFaturado = true
                break;
            }
        }
        return itemFaturado;
    }

    /*
    * Verifica se o level da linha modificada é o 3, e trata e calcula valor do elemento pai do pai(level 1)
    */
    trataElementoPaidoPai(linha:any, elementoPai:any){
        if(linha.level!=2){
            let elementoPaidoPai = null;
            this.treetest.forEach(element => {
                if(element.obj.id==elementoPai.obj.pai){
                    elementoPaidoPai=element;
                    element.obj.valorOrcamentoRealDefinido = true;
                    element.obj.valorOrcamentoRealDisabled = true;
                }
            });
            if(elementoPaidoPai!=null){
                elementoPaidoPai.obj.orcamentoReal = this.recalcularTotalColuna(elementoPaidoPai);
            }
        }
    }
    trataElementoPaidoPaiValorReceber(linha:any, elementoPai:any){
        if(linha.level!=2){
            let elementoPaidoPai = null;
            this.treetest.forEach(element => {
                if(element.obj.id==elementoPai.obj.pai){
                    elementoPaidoPai=element;
                }
            });
            if(elementoPaidoPai!=null){
                elementoPaidoPai.obj.valorreceber = this.recalcularTotalColunaValorReceber(elementoPaidoPai);
            }
        }
    }

    /*
    * Recalcula o somatório total do orcamento real
    */
    recalcularOrcamentoRealTotal(){
        this.orcamentoRealTotal=0;

        this.treetest.forEach(element => {

            //Só somo seu valor se ele for uma propostaitem, ou seja, não tem pai
            if(element._parentId_ == null && element.obj.valorOrcamentoRealDefinido==true && element.obj.orcamentoReal !== null && element.obj.orcamentoReal !== undefined){
                this.orcamentoRealTotal+=Number(element.obj.orcamentoReal);
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
            /*Adiciono ao somatório total de desconto, apenas se o nó não for propostaitem, para não somar o valor dos filhos com os pais
              Também poderia somar só o valor das propostasitens, também daria certo
            */
            if(element.obj.desconto !== null && element.obj.desconto !== undefined && element.obj.tipo === 'propostaitem'){
                this.descontoTotal+=Number(element.obj.desconto);
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
            /*Adiciono ao somatório total de acrescimo, apenas se o nó não for propostaitem, para não somar o valor dos filhos com os pais
              Também poderia somar só o valor das propostasitens, também daria certo
            */
            if(element.obj.acrescimo !== null && element.obj.acrescimo !== undefined && element.obj.tipo === 'propostaitem'){
                this.acrescimoTotal+=Number(element.obj.acrescimo);
            }
        });
        this.recalcularOrcamentoTotal();
    } 
    
    /*
    * Recalcula o orcamento total
    */
    recalcularOrcamentoTotal(){
        this.orcamentoTotal = this.orcamentoRealTotal - this.descontoTotal + this.acrescimoTotal;
        this.reloadScope();
    } 

  
    /**
     * Carrega fichas financeiras
     * */
     async getOrcamentosPreencher() {
        this.busyOrcamentosPreencher = true;

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
                nodeOrcamento.orcamentoReal = nodeOrcamento.valor;
                nodeOrcamento['valorOrcamentoRealDefinido'] = true
                nodeOrcamento['editDisable'] = editDisablePropostaitem;
                if(nodeOrcamento.possuifilho==true){
                    nodeOrcamento['valorOrcamentoRealDisabled'] = true //SE TEM FILHO, BLOQUEIA O VALOR TOTAL
                } else {
                    nodeOrcamento['valorOrcamentoRealDisabled'] = false //SE NÃO, HABILITA O VALOR TOTAL
                }

            } else if(orcamentoProvisorio.propostaitemfamilia != null && orcamentoProvisorio.propostaitemfuncao == null) {
                //node familia
                nodeOrcamento = this.obterNodePropostaitemFamilias(orcamentoProvisorio)

            } else if(orcamentoProvisorio.propostaitemfamilia == null && orcamentoProvisorio.propostaitemfuncao != null) {
                //node funcao
                nodeOrcamento = this.obterNodePropostaitemFuncoes(orcamentoProvisorio);
            }

            //REDEFINE O EDIT DISABLE CONFORME O EDIT DISABLE DO NODE EXECUCAO DE SERVICO
            if(nodeOrcamento.editDisable == false){
                editDisablePropostaitem = false;
            }

            //DA UM PUSH
            myList.push(nodeOrcamento);

        }

        // //SETA A LISTA NO LOCAL.
        this.lstOrcamentosPreencher = myList;

        // PARA CADA ORÇAMENTO
        for (const orcamento of this.lstOrcamentosPreencher) {
            //OBTEM O NODE E PUSHA O NODE NA TREETEST
            /** 
             * @todo condicional para remoção: não ser pai  
             * */
            const pai = orcamento.pai? orcamento.pai : null ;
            const noNovo = this.obterNovoNode(orcamento.tipo, orcamento, pai)

            // passo minha lista `treeTest` apenas fazer push de itens da proposta;
            // await this.obterPropostasItens(ficha, propostaindex)
            this.treetest.push(noNovo);

        }

        //RECALCULAR O VALOR DO ORÇAMENTO REAL, DESCONTO E ACRÉSCIMO DE CADA PROPOSTAITEM
        for(let i = 0; i < this.treetest.length; i++){
            //ENCONTRA O PAI
            if(this.treetest[i].tipo == 'propostaitem'){
                //ATUALIZA O VALOR
                this.atualizaValorPai(this.treetest[i]._id_,'orcamentoReal');
                this.atualizaValorPai(this.treetest[i]._id_,'desconto');
                this.atualizaValorPai(this.treetest[i]._id_,'acrescimo');
                this.atualizaValorPai(this.treetest[i]._id_,'valorreceber');
            }
        }

        ////// sempre que terminar a requisição para preencher a tree, deve-se calcular as somas totais:
        //RODA ALGUMAS ROTINAS...
        this.recalcularDescontoTotal();
        this.recalcularAcrescimoTotal();
        this.recalcularOrcamentoRealTotal();

        this.reloadScope();
        this.busyOrcamentosPreencher = false;

    }

    // /**
    //  * Carrega fichas financeiras
    //  * */
    //  async getOrcamentosPreencher() {
    //     this.busyOrcamentosPreencher = true;

    //     let myList = [];
    //     for (let i = 0; i < this.entity.length; i++) {
    //         const propostaitem = this.entity[i];
    //         const propostaItensFuncoes: any = await this.carregaPropostasitensFuncoes(propostaitem.propostaitem);
    //         const propostaItensFamilias: any = await this.carregaPropostasitensFamilias(propostaitem.propostaitem);
    //         propostaitem.propostasitensfamilias = propostaItensFamilias;
    //         propostaitem.propostasitensfuncoes = propostaItensFuncoes;
    //         let somaItensFamilia: number = 0
    //         let editDisablePropostaitem = true;

    //         const propostaItensOrcamentos: any = await this.carregaOrcamento(propostaitem);

    //         //localizando orcamento do propostaitem
    //         const orcamentoPropostaItem = propostaItensOrcamentos.find(element => {
    //             if(
    //                 element.propostaitemfamilia.propostaitemfamilia == null &&
    //                 element.propostaitemfuncao.propostaitemfuncao == null &&
    //                 // propostaitem.servicoorcamento.orcamento != element.orcamento
    //                 element.execucaodeservico !== true
    //             ){ return true; }
    //         });

    //         //localizando orcamento da execucao de servico
    //         const orcamentoExecucaoServico = propostaItensOrcamentos.find(element => {
    //             if(
    //                 element.propostaitemfamilia.propostaitemfamilia == null &&
    //                 element.propostaitemfuncao.propostaitemfuncao == null &&
    //                 // propostaitem.servicoorcamento.orcamento == element.orcamento
    //                 element.execucaodeservico === true
    //             ){ return true; }
    //         });

    //         let nodePropostaitem = this.obterNodePropostaitem(propostaitem, orcamentoPropostaItem);
    //         let nodeExecucaoServico = this.obterNodeExecucaoServico(propostaitem, orcamentoExecucaoServico);
    //         if(nodeExecucaoServico.editDisable == false){
    //             editDisablePropostaitem = false;
    //         }
    //         myList.push(nodePropostaitem);
    //         myList.push(nodeExecucaoServico);

    //         let somaItensFuncaoOrcamento: number = 0;
    //         propostaItensFuncoes.forEach(funcao => {
    //             const orcamentoFuncao = propostaItensOrcamentos.find(element => {
    //                 if(
    //                     element.propostaitemfamilia.propostaitemfamilia == null &&
    //                     element.propostaitemfuncao.propostaitemfuncao == funcao.propostaitemfuncao
    //                 ){ return true; }
    //             });
    //             let nodeFuncao = this.obterNodePropostaitemFuncoes(funcao, orcamentoFuncao);
    //             myList.push(nodeFuncao);
    //             if(nodeFuncao.editDisable == false){
    //                 editDisablePropostaitem = false;
    //             }
    //             if(orcamentoFuncao !== undefined){
    //                 somaItensFuncaoOrcamento += parseFloat(orcamentoFuncao.valor);
    //             }
    //         });

    //         let somaItensFamiliaOrcamento: number = 0;
    //         propostaItensFamilias.forEach(familia => {
    //             const orcamentoFamilia = propostaItensOrcamentos.find(element => {
    //                 if(
    //                     element.propostaitemfuncao.propostaitemfuncao == null &&
    //                     element.propostaitemfamilia.propostaitemfamilia == familia.propostaitemfamilia
    //                 ){ return true; }
    //             });
    //             let nodeFamilia = this.obterNodePropostaitemFamilias(familia, orcamentoFamilia)
    //             myList.push(nodeFamilia);
    //             if(nodeFamilia.editDisable == false){
    //                 editDisablePropostaitem = false;
    //             }
    //             if(orcamentoFamilia !== undefined){
    //                 somaItensFamiliaOrcamento += parseFloat(orcamentoFamilia.valor);
    //             }
    //         });

    //         let valorOrcamentoReal: number = 0;
    //         if(orcamentoPropostaItem !== undefined){
    //             let valorExecucao:number = (orcamentoExecucaoServico !== undefined && orcamentoExecucaoServico.valor !== undefined) ? parseFloat(orcamentoExecucaoServico.valor) : 0;
    //             // valorOrcamentoReal = somaItensFamiliaOrcamento + somaItensFuncaoOrcamento + valorExecucao;
    //             valorOrcamentoReal = orcamentoPropostaItem.valor;
    //         } else {
    //             //percorro cada propostaitemfamilia, somando o valor de cada uma
    //             propostaItensFamilias.forEach(itemfamilia => {
    //                 somaItensFamilia += parseFloat(itemfamilia.valor)
    //             });
    //             //Somando o valor das propostasitensfamilias com o item de faturamento da propostaitem
    //             valorOrcamentoReal = somaItensFamilia + parseFloat(propostaitem.itemdefaturamentovalor)
    //         }
            

    //         //Modifico o valor do orçamento real na lista
    //         myList.forEach(element => {
    //             if(element.id === propostaitem.propostaitem){
    //                 element.orcamentoReal = valorOrcamentoReal
    //                 element['valorOrcamentoRealDefinido'] = true
    //                 element['editDisable'] = editDisablePropostaitem;                    

    //                 if(element.possuifilho==true){
    //                     element['valorOrcamentoRealDisabled'] = true
    //                 } else {
    //                     element['valorOrcamentoRealDisabled'] = false
    //                 }

    //             } 
    //         });

    //     }
    //     this.lstOrcamentosPreencher = myList;

    //     for (const orcamento of this.lstOrcamentosPreencher) {
    //         /** 
    //          * @todo condicional para remoção: não ser pai  
    //          * */
    //         const pai = orcamento.pai? orcamento.pai : null ;
    //         const noNovo = this.obterNovoNode(orcamento.tipo, orcamento, pai)

    //         // passo minha lista `treeTest` apenas fazer push de itens da proposta;
    //         // await this.obterPropostasItens(ficha, propostaindex)
    //         this.treetest.push(noNovo);

    //     }

    //     //RECALCULAR O VALOR DO ORÇAMENTO REAL, DESCONTO E ACRÉSCIMO DE CADA PROPOSTAITEM
    //     for(let i = 0; i < this.treetest.length; i++){
    //         if(this.treetest[i].tipo == 'propostaitem'){
    //             this.atualizaValorPai(this.treetest[i]._id_,'orcamentoReal');
    //             this.atualizaValorPai(this.treetest[i]._id_,'desconto');
    //             this.atualizaValorPai(this.treetest[i]._id_,'acrescimo');
    //             this.atualizaValorPai(this.treetest[i]._id_,'valorreceber');
    //         }                    
    //     }

    //     ////// sempre que terminar a requisição para preencher a tree, deve-se calcular as somas totais:
    //     this.recalcularDescontoTotal();
    //     this.recalcularAcrescimoTotal();
    //     this.recalcularOrcamentoRealTotal();

    //     this.reloadScope();
    //     this.busyOrcamentosPreencher = false;

    // }

    obterNodePropostaitemFuncoes (orcamento) {
        //defaults
        let valorOrcamentoRealDefinido = true;
        let valorOrcamentoRealDisabled = false;
        let valorReceberDefinido: boolean = false;
        let idOrcamento = null;
        let valorreceber = 0;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorreceberOriginal = valorreceber;
        let valorOriginal = orcamentoReal;
        let descontoOriginal = desconto;
        let acrescimoOriginal = acrescimo;
        let faturar: boolean;
        let faturarOriginal: boolean;
        let editDisable = false;
        let tipoAtualizacao = null;
        let tipoAtualizacaoOriginal = null;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            valorreceber = parseFloat(orcamento.valorreceber);
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorreceberOriginal = valorreceber;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
            faturar = orcamento.faturar;
            faturarOriginal = faturar;
            tipoAtualizacao = orcamento.tipoatualizacao;
            tipoAtualizacaoOriginal = tipoAtualizacao;
            if(orcamento.valorreceber !== orcamento.valor){
                valorReceberDefinido = true;
            }
        }
        if(orcamento.itemcontrato !== null ){
            editDisable = true;
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
            "valorReceberDefinido": valorReceberDefinido,
            "editDisable": editDisable,
            "valorreceber": valorreceber,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": orcamento.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "original": {
                "valorreceber": valorreceberOriginal,
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
                "faturar": faturarOriginal,
                "tipoatualizacao": tipoAtualizacaoOriginal
            },
            "faturar": (faturar != undefined && faturar != null) ? faturar : true,
            "tipoatualizacao": tipoAtualizacao,
            "excluido": tipoAtualizacao === 0 ? true : false,
            "adicionado": tipoAtualizacao === 1 ? true : false
        };
        return newItem;
    }

    obterNodePropostaitemFamilias (orcamento) {
        let valorOrcamentoRealDefinido = true;
        let valorOrcamentoRealDisabled = false;
        let valorReceberDefinido: boolean = false;
        let idOrcamento;
        let valorreceber = 0;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorreceberOriginal = 0;
        let valorOriginal = 0;
        let descontoOriginal = 0;
        let acrescimoOriginal = 0;
        let faturar: boolean;
        let faturarOriginal: boolean;
        let editDisable = false;
        let tipoAtualizacao = null;
        let tipoAtualizacaoOriginal = null;
        let excluido: boolean;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            valorreceber = parseFloat(orcamento.valorreceber);
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorreceberOriginal = valorreceber;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
            faturar = orcamento.faturar;
            faturarOriginal = faturar;
            tipoAtualizacao = orcamento.tipoatualizacao;
            tipoAtualizacaoOriginal = tipoAtualizacao;
            if(orcamento.valorreceber !== orcamento.valor){
                valorReceberDefinido = true;
            }
        } else if (orcamento.valor!=null || orcamento.valor!=undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = null;
            valorreceber = parseFloat(orcamento.valor);
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = 0;
            acrescimo = 0;
        }
        if(orcamento.itemcontrato !== null){
            editDisable = true;
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
            "valorReceberDefinido": valorReceberDefinido,
            "editDisable": editDisable,
            "valorreceber": valorreceber,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": orcamento.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": true,
            "children": [],
            "original": {
                "valorreceber": valorreceberOriginal,
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
                "faturar": faturarOriginal,
                "tipoatualizacao": tipoAtualizacaoOriginal
            },
            "faturar": (faturar != undefined && faturar != null) ? faturar : true,
            "tipoatualizacao": tipoAtualizacao,
            "excluido": tipoAtualizacao === 0 ? true : false,
            "adicionado": tipoAtualizacao === 1 ? true : false
        };
        return newItem;
    }

    obterNodeExecucaoServico (orcamento) {
        let valorOrcamentoRealDefinido: boolean = true;
        let valorOrcamentoRealDisabled: boolean = false;
        let valorReceberDefinido: boolean = false;
        let idOrcamento = null;
        let valorreceber = 0;
        let orcamentoReal: number = orcamento.itemdefaturamentovalor;
        let desconto = 0;
        let acrescimo = 0;
        let valorreceberOriginal = 0;
        let valorOriginal = 0;
        let descontoOriginal = 0;
        let acrescimoOriginal = 0;
        let faturar: boolean;
        let faturarOriginal: boolean;
        let editDisable = false;
        let tipoAtualizacao = null;
        let tipoAtualizacaoOriginal = null;

        //verificar se tem orçamento para carregar os dados:
        if (orcamento !== undefined) {
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            valorreceber = parseFloat(orcamento.valorreceber);
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorreceberOriginal = valorreceber;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
            faturar = orcamento.faturar;
            faturarOriginal = faturar;
            tipoAtualizacao = orcamento.tipoatualizacao;
            tipoAtualizacaoOriginal = tipoAtualizacao;
            if(orcamento.valorreceber !== orcamento.valor){
                valorReceberDefinido = true;
            }     
        }
        if(orcamento.itemcontrato !== null){
            editDisable = true;
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
            "valorReceberDefinido": valorReceberDefinido,
            "editDisable": editDisable,
            "valorreceber": valorreceber,
            "orcamentoReal": orcamentoReal ? orcamentoReal : 0, //Inicializar o orcamento com zero quando ele não tem valor
            "servicosProdutos": 'Execução do Serviço',
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "original": {
                "valorreceber": valorreceberOriginal,
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
                "faturar": faturarOriginal,
                "tipoatualizacao": tipoAtualizacaoOriginal
            },
            "faturar": (faturar != undefined && faturar != null) ? faturar : true,
            "tipoatualizacao": tipoAtualizacao,
            "excluido": tipoAtualizacao === 0 ? true : false
        };
        return newItem;
    }

    obterNodePropostaitem (orcamento) {
        let valorOrcamentoRealDefinido = false;
        let valorOrcamentoRealDisabled = true;
        let valorReceberDefinido: boolean = false;
        let idOrcamento = null;
        let valorreceber = 0;
        let orcamentoReal = 0;
        let desconto = 0;
        let acrescimo = 0;
        let valorreceberOriginal = 0;
        let valorOriginal = 0;
        let descontoOriginal = 0;
        let acrescimoOriginal = 0;
        let possuiFilho = false;
        let faturar: boolean = null;
        let tipoAtualizacaoOriginal = null;
        let tipoatualizacao = null; //Sempre nulo porque por enquanto não posso excluir a propostaitem diretamente

        possuiFilho = true;

        //verificar se tem orçamento para carregar os dados:

        if (orcamento !== undefined) { //mudar
            valorOrcamentoRealDefinido = true;
            valorOrcamentoRealDisabled = false;
            idOrcamento = orcamento.orcamento;
            valorreceber = parseFloat(orcamento.valorreceber);
            orcamentoReal = parseFloat(orcamento.valor);
            desconto = parseFloat(orcamento.desconto);
            acrescimo = parseFloat(orcamento.acrescimo);
            valorreceberOriginal = valorreceber;
            valorOriginal = orcamentoReal;
            descontoOriginal = desconto;
            acrescimoOriginal = acrescimo;
            faturar = faturar;
            tipoatualizacao = tipoatualizacao;
            if(orcamento.valorreceber !== orcamento.valor){
                valorReceberDefinido = true;
            }
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
            "valorReceberDefinido": valorReceberDefinido,
            "valorreceber": valorreceber,
            "orcamentoReal": orcamentoReal,
            "servicosProdutos": orcamento.nome,
            "cobertura": "",
            "orcamentoPrevisto": 0,
            "desconto": desconto,
            "acrescimo": acrescimo,
            "produto": false,
            "children": [],
            "observacaocomposicao": orcamento.observacaocomposicao,
            "original": {
                "valorreceber": valorreceberOriginal,
                "valor": valorOriginal,
                "desconto": descontoOriginal,
                "acrescimo": acrescimoOriginal,
                "faturar": faturar,
                "tipoatualizacao": tipoatualizacao
            },
            "faturar": faturar,
            "tipoatualizacao": tipoatualizacao
        }
        return newItem;
    }

    reloadScope() {
        this.$scope.$applyAsync();
        const treeFormated = this.tree.getArvore(this.treetest);
        this.lstOrcamentosPreencher = treeFormated;
       
    }

    traduzMetodo(metodo) {
        return eval(metodo);
    };

    /** Informações de local de velorio e sepultamento **/
    getOrcamentosPreencherInfo(){
        this.locaisOrcamentosPreencherInfo = {
            "id": 1,
            "localSepultamento": {
                "pais": "Brasil",
                "estado": "Rio de Janeiro",
                "cidade": "Rio de Janeiro",
                "cemiterio": "Lorem Ipsum",
                "sepultamento": "Jazigo",
            },
            "localVelorio1": {
                "pais": "Brasil",
                "estado": "Rio de Janeiro",
                "cidade": "Rio de Janeiro",
                "capela": "Candelária",
                "previsaoInicio": "2019-06-05 14:03:00",
                "previsaoFim": "2019-06-05 14:03:00",
            },
            "localVelorio2": {
                "pais": "Brasil",
                "estado": "Rio de Janeiro",
                "cidade": "Rio de Janeiro",
                "capela": "Candelária",
                "previsaoInicio": "2019-06-05 14:03:00",
                "previsaoFim": "2019-06-05 14:03:00",
            },
        };

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

    /* Muda o estado do checkbox da coluna faturar */
    mudaEstadoCheckFaturar(linha: any){
        let idElemento = linha.branch._id_
        let statusFaturar = !linha.branch.obj.faturar

        for(let i = 0; i < this.treetest.length; i++){

            //Desmarcando checkbox. Se eu desmarco, tenho que zerar os valores desse elemento, se ele não tiver excluído
            if((this.treetest[i]._id_ == idElemento) && statusFaturar == false && this.treetest[i].obj.excluido != true){
                this.treetest[i].obj.faturar = statusFaturar;
                this.treetest[i].obj.acrescimo = this.treetest[i].obj.desconto = this.treetest[i].obj.orcamentoReal = this.treetest[i].obj.valorreceber = 0;
                // this.salvarValorDigitado(linha, 'orcamentoReal');
                this.atualizaValorPai(linha.pai, 'desconto');
                this.atualizaValorPai(linha.pai, 'acrescimo');
                this.atualizaValorPai(linha.pai, 'orcamentoReal');
                this.atualizaValorPai(linha.pai, 'valorreceber');
                this.salvarValorDigitado(linha, 'desconto');
                this.salvarValorDigitado(linha, 'acrescimo');
                this.salvarValorDigitado(linha, 'orcamentoReal');
                this.salvarValorDigitado(linha, 'valorreceber');
                break;

            }
            //Marcando checkbox. Apenas mudo o status do checkbox faturar
            else if(this.treetest[i]._id_ == idElemento){
                this.treetest[i].obj.faturar = statusFaturar;
                break;
            }
        }

    }

    /**
     * dispara evento crm_atcsfichafinanceira_refresh em ficha-financeira.ts
     */
    callRefresh(){
        this.$rootScope.$broadcast('crm_atcsfichafinanceira_refresh', {
        });
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * processa os orçamentos, retornando um objeto javascript pronto para disparo bulk.
     */
    processarOrcamentos () {
        let orcamentos = {"orcamentos": []};
        this.treetest.forEach(element => {
            let valor = element.obj.orcamentoReal;
            if(element.obj.valorOrcamentoRealDefinido == false){
                valor = 0;
            } else if(element.obj.orcamentoReal == null){
                valor = 0;
            } else if(element.obj.orcamentoReal !== 0) {
                valor = element.obj.orcamentoReal;
            }
            let item = {
                "orcamento": element.obj.orcamento,
                "fornecedor": {
                    "fornecedor": this.entity.fornecedor
                },
                "negocio": this.entity.atc,
                "proposta": this.entity.proposta,
                "propostaitem": null,
                "propostaitemfamilia": null,
                "propostaitemfuncao": null,
                "itemfaturamento": null,
                "execucaodeservico": false,
                "valorreceber": element.obj.valorreceber,
                "valor": valor,
                "desconto": element.obj.desconto,
                "acrescimo": element.obj.acrescimo,
                "original" : {
                    "valorreceber": parseFloat(element.obj.original.valorreceber),
                    "valor": parseFloat(element.obj.original.valor),
                    "desconto": parseFloat(element.obj.original.desconto),
                    "acrescimo": parseFloat(element.obj.original.acrescimo),
                    "faturar": element.obj.original.faturar,
                    "tipoatualizacao": (element.obj.original.tipoatualizacao != undefined && element.obj.original.tipoatualizacao != null) ? element.obj.original.tipoatualizacao : null
                },
                "faturar": element.obj.faturar,
                "tipoatualizacao": (element.obj.tipoatualizacao != undefined && element.obj.tipoatualizacao != null) ? element.obj.tipoatualizacao : null
            };
            // TODO SE DEFINIDO = TRUE E NÃO TEM VALOR, NÃO DEVE PERMITIR ENVIAR.
            switch (element.obj.tipo) {
                case 'propostaitem':
                    item.propostaitem = {
                        "propostaitem": element.obj.id
                    };
                    break;
                case 'itemdefaturamento':
                    item.execucaodeservico = true;
                    item.propostaitem = {
                        "propostaitem": element.obj.pai
                    };
                    break;
                case 'propostaitemfamilia':
                    item.propostaitemfamilia = {
                        'propostaitemfamilia': element.obj.id
                    };
                    item.propostaitem = {
                        "propostaitem": element.obj.pai
                    };
                    break;
                case 'propostaitemfuncao':
                    item.propostaitemfuncao = {
                        'propostaitemfuncao': element.obj.id
                    };
                    item.propostaitem = {
                        "propostaitem": element.obj.pai
                    };
                    break;
            }
            orcamentos.orcamentos.push(item);
        });
        return orcamentos;
    }

    /**
     * Dispara a criação de um novo orçamento, retornando [true, orcamento] em caso de sucesso, ou [false,error] em caso de fracasso
     * @param orcamento 
     */
    async orcamentosCreate(orcamento){
        return await this.$http({
            method: 'POST',
            data: angular.copy(orcamento),
            url: this.nsjRouting.generate('crm_orcamentos_create', angular.extend({}, {}, { 'offset': '', 'filter': '' }), true),
        })
        .then((response) => {
            orcamento.orcamento = response.data.orcamento;
            return [true,orcamento];
        })
        .catch((error) => {
            return [false,error];
        });
    }

    /**
     * Dispara a atualização de um orçamento existente, retornando [true,response] em caso de sucesso, ou [false,error] em caso de fracasso
     * @param orcamento 
     */
    async orcamentosPut(orcamento){
        return await this.$http({
            method: 'PUT',
            data: angular.copy(orcamento),
            url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
        })
        .then((response) => {
            return [true,response];
        })
        .catch((error) => {
            return [false,error];
        });
    }

    /**
     * Dispara a função "enviar" de um orçamento existente, retornando [true,response] em caso de sucesso, ou [false,error] em caso de fracasso
     * @param orcamento 
     */
    async orcamentosEnviar(orcamento){
        return await this.$http({
            method: 'POST',
            data: angular.copy(orcamento),
            url: this.nsjRouting.generate('crm_orcamentos_enviar', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
        })
        .then((response) => {
            return [true,response];
        })
        .catch((error) => {
            return [false,error];
        });
    }

    /**
     * Dispara a função "aprovar" de um orçamento existente, retornando [true,response] em caso de sucesso, ou [false,error] em caso de fracasso
     * @param orcamento 
     */
    async orcamentosAprovar(orcamento){
        return await this.$http({
            method: 'POST',
            data: angular.copy(orcamento),
            url: this.nsjRouting.generate('crm_orcamentos_aprovar', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
        })
        .then((response) => {
            this.$rootScope.$broadcast('crm_orcamentos_aprovar', orcamento);
            return [true,response];
        })
        .catch((error) => {
            return [false,error];
        });
    }

    /**
     * Itera sobre os orçamentos, atualizando o status para 1(enviado). Caso não tenha sido criado do db, os cria.
     */
    async enviarOrcamento(){
        this.busyOrcamentosPreencher = true;
        let orcamentos = this.processarOrcamentos();
        let erros = false;
        for (let index = 0; index < orcamentos.orcamentos.length; index++) {
            let pular = false;
            let orcamento = orcamentos.orcamentos[index];
            if(orcamento.orcamento === null) {
                //disparo de criacao
                const orcamentosCreateResponse = await this.orcamentosCreate(orcamento);
                if(orcamentosCreateResponse[0] === false) {
                    this.toaster.pop({
                        type: 'error',
                        title: orcamentosCreateResponse[1]
                    });
                    erros = true;
                    this.busyOrcamentosPreencher = false;
                }
            } else {
                //checar se existe diferença nos valores para não disparar updates desnecessarios
                if(orcamento.original.valor == orcamento.valor && 
                    orcamento.original.desconto == orcamento.desconto && 
                    orcamento.original.acrescimo == orcamento.acrescimo &&
                    orcamento.original.faturar == orcamento.faturar) {
                    pular = true;
                }
                if(pular === false) {
                    //disparo de atualização
                    const orcamentosPutResponse = await this.orcamentosPut(orcamento);
                    if(orcamentosPutResponse[0] === false) {
                        this.toaster.pop({
                            type: 'error',
                            title: orcamentosPutResponse[1]
                        });
                        erros = true;
                        this.busyOrcamentosPreencher = false;
                    }
                }
            }
            //disparo de enviar
            const orcamentosEnviarResponse = await this.orcamentosEnviar(orcamento);
            if(orcamentosEnviarResponse[0] === false) {
                this.toaster.pop({
                    type: 'error',
                    title: orcamentosEnviarResponse[1]
                });
                erros = true;
                this.busyOrcamentosPreencher = false;
            }
        }
        this.close();
        if(!erros){
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao enviar orçamento.'
            });
            this.callRefresh();
        }
    }

    async aprovarTodosOsOrcamentos () {
        let data = {
            'proposta': this.entity.proposta,
            'fornecedor': {
                'fornecedor': this.entity.fornecedor
            }
        };
        return new Promise((resolve, reject) => {
            this.$http({
            method: 'POST',
            data: angular.copy(data),
            url: this.nsjRouting.generate(
                'crm_fornecedoresenvolvidos_aprovar_orcamentos_negocio_fornecedor', 
                angular.extend(
                    {}, 
                    { 'negocio': this.entity.atc, 'id': this.entity.fornecedorEnvolvido },
                    { 'offset': '', 'filter': '' }
                ),
                true),
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                /*reject(erro);*/
                this.toaster.pop({
                    type: 'error',
                    title: erro.data.message
                });
                this.busyOrcamentosPreencher = false;
            });
        });
    }

    permiteOrcamentoZerado(){
        if(this.entity.listagemOrcamentos[0].permiteorcamentozerado === true &&
            this.entity.listagemOrcamentos[0].estabelecimentoid !== null &&
            this.entity.listagemOrcamentos[0].estabelecimentoid !== undefined
        ){
            return true;
        }
        return false;
    }

    /**
     * Itera sobre os orçamentos, atualizando o status para 2(aprovado). Caso não tenha sido criado do db, os cria.
     * 
     */
    async aceitarOrcamento(){
        this.busyOrcamentosPreencher = true;
        let orcamentos = this.processarOrcamentos();
        let temItemFaturado = this.verificaItemFaturado(orcamentos);
        let propostasItensAExcluir = [];
        let excluirOrcamentoDefinitivamente = false; //define se deverá disparar request para excluir orçamento definitivamente.

        //não aceita itens não faturados
        if(temItemFaturado == false){
            this.toaster.pop({
                type: 'error',
                title: 'Não é possível aceitar orçamento sem item faturado.'
            });
            this.busyOrcamentosPreencher = false;
        }

        let erros = false;
        if (temItemFaturado == true){

            if(!this.permiteOrcamentoZerado()){
                //para cada orcamento, verifica se tem valores zerados. se sim, encerra.
                for (let index = 0; index < orcamentos.orcamentos.length; index++) {
                    let orcamento = orcamentos.orcamentos[index];
                    if(orcamento.valor <= 0 && orcamento.faturar == true){
                        this.toaster.pop({
                            type: 'error',
                            title: 'Existem itens com valor Orçamento Real zerado. Não é possível aceitar.'
                        });
                        this.busyOrcamentosPreencher = false;
                        return;
                    }
                }
            }

            //para cada orçamento
            for (let index = 0; index < orcamentos.orcamentos.length; index++) {
                let pular = false;
                let orcamento = orcamentos.orcamentos[index];
                
                //se o orçamento não existe, tenta criar o orçamento
                if((orcamento.orcamento === null || orcamento.orcamento == undefined)) {
                    //disparo de criacao
                    if(orcamento.tipoatualizacao != 0){
                        const orcamentosCreateResponse = await this.orcamentosCreate(orcamento);
                        if(orcamentosCreateResponse[0] === false) {
                            this.toaster.pop({
                                type: 'error',
                                title: orcamentosCreateResponse[1]
                            });
                            erros = true;
                            this.busyOrcamentosPreencher = false;
                        }
                    }
                }
                else {
                    //ATUALIZAÇÃO AO ACEITAR REMOVIDA
                    // //checar se existe diferença nos valores para não disparar updates desnecessarios
                    // if(orcamento.original.valor == orcamento.valor && 
                    //     orcamento.original.desconto == orcamento.desconto && 
                    //     orcamento.original.acrescimo == orcamento.acrescimo &&
                    //     orcamento.original.faturar == orcamento.faturar &&
                    //     orcamento.tipoatualizacao != 0) {
                    //     pular = true;
                    // }

                    //Mudando tipoatualizacao. Se for 0, mudo para 2 para excluir fisicamente
                    if(orcamento.tipoatualizacao == 0){
                        orcamento.tipoatualizacao = 2;
                        excluirOrcamentoDefinitivamente = true;
                    }

                    //ATUALIZAÇÃO AO ACEITAR REMOVIDA
                    // if(pular === false) {
                    //     //disparo de atualização
                    //     const orcamentosPutResponse = await this.orcamentosPut(orcamento);
                    //     if(orcamentosPutResponse[0] === false) {
                    //         this.toaster.pop({
                    //             type: 'error',
                    //             title: orcamentosPutResponse[1]
                    //         });
                    //         erros = true;
                    //         this.busyOrcamentosPreencher = false;
                    //     }
                    // }
                }
                // // disparo APROVAÇÃO - movido para chamada única abaixo
                // if (orcamento.orcamento != null || orcamento.orcamento != undefined){
                //     const orcamentosAprovarResponse = await this.orcamentosAprovar(orcamento);
                //     if(orcamentosAprovarResponse[0] === false) {
                //         this.toaster.pop({
                //             type: 'error',
                //             title: orcamentosAprovarResponse[1]
                //         });
                //         erros = true;
                //         this.busyOrcamentosPreencher = false;
                //     }
                // }
            }

            // faz a aprovação de todos os orçamentos de uma vez. em caso de erro, para o processo.
            await this.aprovarTodosOsOrcamentos()
            .then((response) => {
                //sucesso ao aprovar os orçamentos
            })
            .catch((error) => {
                erros = true;
                this.toaster.pop({
                    type: 'error',
                    title: 'Ocorreu um eror ao tentar aprovar os orçamentos.'
                });
                this.busyOrcamentosPreencher = false;
                return;
            });
            if(erros) return;


            propostasItensAExcluir = this.verificaTodosOrcamentosExcluidos(orcamentos);
            let orcamento: any;

            for (let index = 0; index < propostasItensAExcluir.length; index++) {
            const element = propostasItensAExcluir[index];
                    
                if(element.excluir == true && element.naoexcluir == false){
                    //Achar o objeto orçamento que eu tenho que enviar
                    for(let i = 0; i < orcamentos.orcamentos.length; i++){
                        if(orcamentos.orcamentos[i].orcamento == element.orcamento){
                            orcamento = orcamentos.orcamentos[i];
                            break;
                        }
                    }
                    //Alterar sua flag tipoatualizacao (excluir)
                    orcamento.tipoatualizacao = 2;
                    excluirOrcamentoDefinitivamente = true;

                    if(orcamento.orcamento != null || orcamento.orcamento != undefined){
                        //Apos isso disparar PUT para atualizar
                        let response = await this.$http({
                            method: 'PUT',
                            data: angular.copy(orcamento),
                            url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
                            }).catch(error => {
                                erros = true;
                                this.toaster.pop({
                                    type: 'error',
                                    title: error
                                });
                                this.busyOrcamentosPreencher = false; 
                            });
                    }
                    
                    
                }
            };

            //excluir orcamentos no backend
            if(excluirOrcamentoDefinitivamente == true) {
                await this.excluindoOrcamentoDoBanco(orcamentos.orcamentos[0]);
            }

            //Desvincular a propostaitem do fornecedor
            propostasItensAExcluir.forEach(async element => {

                if(element.excluir == true && element.naoexcluir == false){
                    //Achar o objeto orçamento que eu tenho que enviar
                    for(let i = 0; i < orcamentos.orcamentos.length; i++){
                        if(orcamentos.orcamentos[i].orcamento == element.orcamento){
                            orcamento = orcamentos.orcamentos[i];
                            break;
                        }
                    }
                    
                    //Apos isso disparar PUT para atualizar
                    await this.desvinculaPropostaitem(orcamento);

                    //Disparando broadcast para ser ouvido no pedidos.ts e limpar o lookup da propostaitem na tree de pedidos
                    this.$rootScope.$broadcast('desvinculaFornecedorTreePedidos', element);

                }

            });

            this.close();
            if(!erros){
                this.toaster.pop({
                    type: 'success',
                    title: 'O Orçamento foi aprovado com sucesso!'
                });
                this.callRefresh();
            }
        }
    }

    processaOrcamento(orcamento){
        let valor = orcamento.orcamentoReal;
            if(orcamento.valorOrcamentoRealDefinido == false){
                valor = 0;
            } else if(orcamento.orcamentoReal == null){
                valor = 0;
            } else if(orcamento.orcamentoReal !== 0) {
                valor = orcamento.orcamentoReal;
            }
            let item = {
                "orcamento": orcamento.orcamento,
                "fornecedor": {
                    "fornecedor": this.entity.fornecedor
                },
                "negocio": this.entity.atc,
                "proposta": this.entity.proposta,
                "propostaitem": null,
                "propostaitemfamilia": null,
                "propostaitemfuncao": null,
                "itemfaturamento": null,
                "execucaodeservico": false,
                "valorreceber": orcamento.valorreceber,
                "valor": valor,
                "desconto": orcamento.desconto,
                "acrescimo": orcamento.acrescimo,
                "original" : {
                    "valorreceber": parseFloat(orcamento.original.valorreceber),
                    "valor": parseFloat(orcamento.original.valor),
                    "desconto": parseFloat(orcamento.original.desconto),
                    "acrescimo": parseFloat(orcamento.original.acrescimo),
                    "faturar": orcamento.original.faturar,
                    "tipoatualizacao": (orcamento.original.tipoatualizacao != undefined && orcamento.original.tipoatualizacao != null) ? orcamento.original.tipoatualizacao : null
                },
                "faturar": orcamento.faturar,
                "tipoatualizacao": (orcamento.tipoatualizacao != undefined && orcamento.tipoatualizacao != null) ? orcamento.tipoatualizacao : null
            };
            // TODO SE DEFINIDO = TRUE E NÃO TEM VALOR, NÃO DEVE PERMITIR ENVIAR.
            switch (orcamento.tipo) {
                case 'propostaitem':
                    item.propostaitem = {
                        "propostaitem": orcamento.id
                    };
                    break;
                case 'itemdefaturamento':
                    item.execucaodeservico = true;
                    item.propostaitem = {
                        "propostaitem": orcamento.pai
                    };
                    break;
                case 'propostaitemfamilia':
                    item.propostaitemfamilia = {
                        'propostaitemfamilia': orcamento.id
                    };
                    item.propostaitem = {
                        "propostaitem": orcamento.pai
                    };
                    break;
                case 'propostaitemfuncao':
                    item.propostaitemfuncao = {
                        'propostaitemfuncao': orcamento.id
                    };
                    item.propostaitem = {
                        "propostaitem": orcamento.pai
                    };
                    break;
            }
            return item;
    }

    findNode (idNode) {
        for(let i = 0; i < this.treetest.length; i++){
            if (this.treetest[i].obj.id == idNode){
                return this.treetest[i].obj;
            }
        }
        return null;
    }

    async orcamentosAtualizar(orcamento){
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'PUT',
            data: angular.copy(orcamento),
            url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    async salvarAlteracoesOrcamento(linha) {

        linha.loading = true;
        let orcamento = this.processaOrcamento(linha)
        if(!this.permiteOrcamentoZerado()){
            if(orcamento.valor <= 0 && orcamento.faturar == true){
                this.toaster.pop({
                    type: 'error',
                    title: 'Este item possui valor Orçamento Real zerado. Não é possível salvar.'
                });
                linha.loading = false; 
                return;
            }
        }

        if(orcamento.orcamento == undefined || orcamento.orcamento == null) {
            // CRIA ORÇAMENTO
            let orcamentosInsertResponse = await this.$http({
                method: 'POST',
                data: angular.copy(orcamento),
                url: this.nsjRouting.generate('crm_orcamentos_create', angular.extend({}, {}, { 'offset': '', 'filter': '' }), true),
            })
            .then((response) => {
                linha.orcamento = response.data.orcamento;
            })
            .catch(error => {
                console.error(error);
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
                linha.loading = false;
                this.$scope.$applyAsync();
                return;
            });
        } else {
            //ATUALIZA ORÇAMENTO
            if(orcamento.original.valor == orcamento.valor && 
                orcamento.original.valorreceber == orcamento.valorreceber && 
                orcamento.original.desconto == orcamento.desconto && 
                orcamento.original.acrescimo == orcamento.acrescimo &&
                orcamento.original.faturar == orcamento.faturar &&
                orcamento.original.tipoatualizacao == orcamento.tipoatualizacao
                ){
                linha.loading = false;
                this.$scope.$applyAsync();
                return;
            }

            //disparo de edicao
            this.orcamentosAtualizar(orcamento)
            .then((response) => {
                //atualiza o original
                this.noAtualizaValorOrigianl(linha.id, orcamento);
                
                let noPai = this.findNode(linha.pai);
                // NÃO ATUALIZA O NÓ CABEÇA POIS O NÓ CABEÇA NÃO É ALTERADO DIRETAMENTE.
                // if(noPai !== null) {
                //     let orcamentoPai = this.processaOrcamento(noPai);
                //     this.noAtualizaValorOrigianl(linha.pai, orcamentoPai);
                // }

                this.toaster.pop({
                    type: 'success',
                    title: 'Valor atualizado!'
                });
                linha.loading = false;
                this.$scope.$applyAsync();
            })
            .catch(error => {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
                linha.loading = false;
                this.$scope.$applyAsync();
                return;
            });
        }
    }

    noAtualizaValorOrigianl(idDoNo, novoObjetoOriginal){
        for(let i = 0; i < this.treetest.length; i++){
            if (this.treetest[i].obj.id == idDoNo){

                this.treetest[i].obj.original.valor = novoObjetoOriginal.valor;
                this.treetest[i].obj.original.valorreceber = novoObjetoOriginal.valorreceber;
                this.treetest[i].obj.original.desconto = novoObjetoOriginal.desconto; 
                this.treetest[i].obj.original.acrescimo = novoObjetoOriginal.acrescimo;
                this.treetest[i].obj.original.faturar = novoObjetoOriginal.faturar;
                this.treetest[i].obj.original.tipoatualizacao = novoObjetoOriginal.tipoatualizacao;
                return true;
            }
        }
        return false;
    }

    /**
     * Dispara requisição com todos os orcamentos para crm_orcamentos_create_bulk
     */
    async salvarOrcamentoBulk () {
        let orcamentos = this.processarOrcamentos();
        let orcamentosInsertResponse = await this.$http({
            method: 'POST',
            data: angular.copy(orcamentos),
            url: this.nsjRouting.generate('crm_orcamentos_create_bulk', angular.extend({}, {}, { 'offset': '', 'filter': '' }), true),
        });
    }

    /**
     * Dispara 1 requisição para cada orçamento, para crm_orcamentos_create, de forma síncrona.
     */
    async salvarOrcamento () {
        this.busyOrcamentosPreencher = true;
        let orcamentos = this.processarOrcamentos();
        let temItemFaturado = this.verificaItemFaturado(orcamentos);
        let propostasItensAExcluir = [];

        if(temItemFaturado == false){
            this.toaster.pop({
                type: 'error',
                title: 'Não é possível salvar orçamento sem item faturado.'
            });
            this.busyOrcamentosPreencher = false;
        }

        //for é sincrono, foreach é asincrono
        let erros = false;
        if (temItemFaturado == true){
            for (let index = 0; index < orcamentos.orcamentos.length; index++) {
                const orcamento = orcamentos.orcamentos[index];
                
                // if(orcamento.valor <= 0 && orcamento.faturar == true){
                //     this.toaster.pop({
                //         type: 'error',
                //         title: 'Existem itens com valor Orçamento Real zerado. Não é possível aceitar.'
                //     });
                //     this.busyOrcamentosPreencher = false; 
                //     return;
                // }
                if(orcamento.orcamento === null || orcamento.orcamento == undefined) {

                    //Se eu excluí o orçamento e ele ainda não existe banco, não preciso gastar uma requisição para criá-lo para depois apagá-lo
                    //Comentado pois ao salvar o orçamento, o orçamento excluído precisa ser salvo no banco
                    // if(orcamento.tipoatualizacao == 0){
                    //     continue;
                    // }

                    //disparo de criacao
                    let orcamentosInsertResponse = await this.$http({
                        method: 'POST',
                        data: angular.copy(orcamento),
                        url: this.nsjRouting.generate('crm_orcamentos_create', angular.extend({}, {}, { 'offset': '', 'filter': '' }), true),
                    }).catch(error => {
                        erros = true;
                        this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                        this.busyOrcamentosPreencher = false;
                    });

                    orcamentos.orcamentos[index].orcamento = orcamentosInsertResponse.data.orcamento; 

                } else {
                    //checar se existe diferença nos valores para não disparar updates desnecessarios
                    if(orcamento.original.valor == orcamento.valor && 
                        orcamento.original.valorreceber == orcamento.valorreceber && 
                        orcamento.original.desconto == orcamento.desconto && 
                        orcamento.original.acrescimo == orcamento.acrescimo &&
                        orcamento.original.faturar == orcamento.faturar &&
                        orcamento.original.tipoatualizacao == orcamento.tipoatualizacao
                        ){
                        continue;
                    }
                    
                    
                    //disparo de edicao
                    let orcamentosInsertResponse = await this.$http({
                        method: 'PUT',
                        data: angular.copy(orcamento),
                        url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
                    }).catch(error => {
                        erros = true;
                        this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                        this.busyOrcamentosPreencher = false; 
                    });
                }
            }

            propostasItensAExcluir = this.verificaTodosOrcamentosExcluidos(orcamentos);
            let orcamento: any;
            //AGORA O SISTEMA, PARA CADA PROPOSTAITEM A EXCLUIR (CABEÇA), ELE LOCALIZA O ORÇAMENTO CABEÇA E ATUALIZA A FLAG PARA 0
            //O SISTEMA DEVERÁ PARA CADA PROPOSTAITEM A EXCLUIR, ATUALIZAR TODOS OS SEUS ORÇAMENTOS COM FLAG PARA 2
            for (let index = 0; index < propostasItensAExcluir.length; index++) {
                const element = propostasItensAExcluir[index];
                if(element.excluir == true && element.naoexcluir == false){

                    //Atualizo a flag tipoatualizacao para 2 (Exclusão Física) de todas os filhos da propostaitem
                    for(let i = 0; i < orcamentos.orcamentos.length; i++){
                        if(orcamentos.orcamentos[i].propostaitem.propostaitem == element.propostaitem){

                            orcamentos.orcamentos[i].tipoatualizacao = 2;

                            //Se é daquela propostaitem, então edito eles no banco
                            await this.$http({
                                method: 'PUT',
                                data: angular.copy(orcamentos.orcamentos[i]),
                                url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamentos.orcamentos[i].orcamento}, { 'offset': '', 'filter': '' }), true),
                            }).catch(error => {
                                erros = true;
                                this.toaster.pop({
                                    type: 'error',
                                    title: error
                                });
                                this.busyOrcamentosPreencher = false; 
                            });


                        }

                    }
 
                    //Achar o objeto orçamento que eu tenho que enviar
                    for(let i = 0; i < orcamentos.orcamentos.length; i++){
                        if(orcamentos.orcamentos[i].orcamento == element.orcamento){
                            orcamento = orcamentos.orcamentos[i];
                            break;
                        }
                    }
                    //Alterar sua flag tipoatualizacao (excluir)
                    orcamento.tipoatualizacao = 2;
                    //Apos isso disparar PUT para atualizar
                    await this.$http({
                        method: 'PUT',
                        data: angular.copy(orcamento),
                        url: this.nsjRouting.generate('crm_orcamentos_put', angular.extend({}, {'id': orcamento.orcamento}, { 'offset': '', 'filter': '' }), true),
                        }).catch(error => {
                            erros = true;
                            this.toaster.pop({
                                type: 'error',
                                title: error
                            });
                            this.busyOrcamentosPreencher = false; 
                        });
                }
            }; 

            //Desvincular a propostaitem do fornecedor
            for (let index = 0; index < propostasItensAExcluir.length; index++) {
                const element = propostasItensAExcluir[index];

                if(element.excluir == true && element.naoexcluir == false){
                    //Achar o objeto orçamento que eu tenho que enviar
                    for(let i = 0; i < orcamentos.orcamentos.length; i++){
                        if(orcamentos.orcamentos[i].orcamento == element.orcamento){
                            orcamento = orcamentos.orcamentos[i];
                            break;
                        }
                    }

                    //excluir orcamentos no backend
                    await this.excluindoOrcamentoDoBanco(orcamento);
                    
                    //Apos isso disparar PUT para atualizar
                    await this.desvinculaPropostaitem(orcamento);

                    //Disparando broadcast para ser ouvido no pedidos.ts e limpar o lookup da propostaitem na tree de pedidos
                    this.$rootScope.$broadcast('desvinculaFornecedorTreePedidos', element);
                }

            };

            this.close();
            if(!erros){
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao salvar orçamento.'
                });
                this.callRefresh();
            }
        }

    }

    //Verifica se todos os orçamentos filhos da propostaitem estão marcados como excluídos
    verificaTodosOrcamentosExcluidos(orcamentos: any){
        let todosExcluidos: boolean = false;
        let obj: any = [];
        for(let i = 0; i < orcamentos.orcamentos.length; i++){

            //Se o orçamento for o orçamento que representa a propostaitem, não faço nada
            if (orcamentos.orcamentos[i].execucaodeservico != true &&
                orcamentos.orcamentos[i].propostaitemfamilia == null &&
                orcamentos.orcamentos[i].propostaitemfuncao == null){
                let propostaItemEncontrado: boolean = false;

                obj.forEach(element => {
                    if(element.propostaitem == orcamentos.orcamentos[i].propostaitem.propostaitem){
                        element.orcamento = orcamentos.orcamentos[i].orcamento;
                        propostaItemEncontrado = true;
                    }
                });

                if(propostaItemEncontrado == false){
                    obj.push({'propostaitem': orcamentos.orcamentos[i].propostaitem.propostaitem, 'excluir': false, 'naoexcluir': false, 'orcamento': orcamentos.orcamentos[i].orcamento});
                }

                continue;
                
            }
            else{
                
                //Se está excluído, mudo para true
                if (orcamentos.orcamentos[i].tipoatualizacao === 0 || orcamentos.orcamentos[i].tipoatualizacao === 2){
                    todosExcluidos = true;
                    let propostaItemEncontrado: boolean = false;

                    //procurar a propostaitem, se não achar, faço o push do obj completo
                    //se achar, eu preciso botar true no atributo excluir
                    obj.forEach(element => {
                        if(element.propostaitem == orcamentos.orcamentos[i].propostaitem.propostaitem){
                            element.excluir = true;
                            propostaItemEncontrado = true;
                        }
                    });

                    if(propostaItemEncontrado == false){
                        obj.push({'propostaitem': orcamentos.orcamentos[i].propostaitem.propostaitem, 'excluir': true, 'naoexcluir': false, 'orcamento': null});
                    }
                    
                    
                    // obj[orcamentos.orcamentos[i].propostaitem.propostaitem]['excluir'] = true
                    //NO objeto criar um indice com o guid da propostaitem. php obj[orcamentos.orcamentos[i].propostaitem]['excluir'] = true
                }
                // //Se um deles não estiver excluído, mudo para false e saio do loop
                else{
                    todosExcluidos = false;
                    let propostaItemEncontrado: boolean = false;
                    // break;
                    //NO objeto criar um indice com o guid da propostaitem. php obj[orcamentos.orcamentos[i].propostaitem]['naoexcluir'] = true
                    

                    //procurar a propostaitem, se não achar, faço o push do obj completo
                    //se achar, eu preciso botar true no atributo excluir
                    obj.forEach(element => {
                        if(element.propostaitem == orcamentos.orcamentos[i].propostaitem.propostaitem){
                            element.naoexcluir = true;
                            propostaItemEncontrado = true;
                        }
                    });

                    if(propostaItemEncontrado == false){
                        obj.push({'propostaitem': orcamentos.orcamentos[i].propostaitem.propostaitem, 'excluir': false, 'naoexcluir': true, 'orcamento': null});
                    }
                    

                }

            }
        }
        return obj;
    }

    //Função desvincula propostaitem do fornecedor
    async desvinculaPropostaitem(orcamento: any){
        //atc, proposta, propostaitem
        let constructor = {
            'atc': orcamento.negocio,
            'proposta': orcamento.proposta,
            'id': orcamento.propostaitem.propostaitem
        };

        await this.$http({
            method: 'POST',
            data: angular.copy(orcamento),
            url: this.nsjRouting.generate('crm_propostasitens_propostas_itens_desvincular_fornecedor', angular.extend({}, constructor, { 'offset': '', 'filter': '' }), true, true),
        }).then((response: any) => {
            this.busyOrcamentosPreencher = false;
        }).catch(error => {
            this.toaster.pop({
                type: 'error',
                title: error
            });
            this.busyOrcamentosPreencher = false;
        });


    }

    async excluindoOrcamentoDoBanco(orcamento: any){
        let constructor = {
            'id': orcamento.propostaitem.propostaitem,
            'proposta':orcamento.proposta, 
            'negocio':orcamento.negocio
         };
        //Mudando o tipoatualizacao no banco
        await this.$http({
            method: 'POST',
            data: angular.copy(orcamento), //atc, proposta, id
            url: this.nsjRouting.generate('crm_propostasitens_orcamentos_excluir', angular.extend({}, constructor, { 'offset': '', 'filter': '' }), true, true),
        }).then((response: any) => {
            this.busyOrcamentosPreencher = false;
        }).catch(error => {
            this.toaster.pop({
                type: 'error',
                title: error
            });
            this.busyOrcamentosPreencher = false;
        });

    }

    verificaAlteracao(linha){

        let classes = '';

        if ((linha.obj.editado != undefined && linha.obj.editado !=null && linha.obj.editado==true) ||
            (linha.obj.adicionado != undefined && linha.obj.adicionado !=null && linha.obj.adicionado==true) ||
            (linha.obj.excluido != undefined && linha.obj.excluido !=null && linha.obj.excluido==true)) {
            classes += ' linhaalterada ';
        } else if (linha.children.length>0) {

            let keepGoing = true;
            linha.children.forEach(filho => {
                if(keepGoing){
                    if ((filho.obj.editado != undefined && filho.obj.editado !=null && filho.obj.editado==true) ||
                    (filho.obj.adicionado != undefined && filho.obj.adicionado !=null && filho.obj.adicionado==true) ||
                    (filho.obj.excluido != undefined && filho.obj.excluido !=null && filho.obj.excluido==true)) {
                        keepGoing=false;
                    }
                }
            });

            if(!keepGoing){
                classes += ' linhaalterada ';
            }
        }

        return classes;
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


export class CrmOrcamentosPreencherModalService {
    static $inject = ['CrmPropostascapitulos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }

    /* Incluindo o pai como parametro opcional*/
    open(parameters: any, subentity: any, paiId: any) {
            return this.$uibModal.open({
                template: require('../../Crm/Atcs/orcamento-preencher-modal.html'),
                controller: 'CrmOrcamentosPreencherModalController',
                controllerAs: 'crm_orcmnts_prnchr_mdl_cntrllr',
                windowClass: 'modal-lg-wrapper',
                resolve: {
                entity: async () => {
                        if (parameters.proposta && parameters.atc && parameters.fornecedor && parameters.fornecedorEnvolvido) {
                            //let entity = this.entityService.get(parameters.proposta, parameters.propostacapitulo);
                            //entity.pai = paiId;
                            let entity = {};
                            let primiseData = {};

                            this.entityService.constructor = {};
                            this.entityService.constructors['negocio'] = parameters.atc;
                            this.entityService.constructors['proposta'] = parameters.proposta;

                            let promiseOrcamentos = this.$http({
                                method: 'GET',
                                url: this.nsjRouting.generate(
                                    'crm_orcamentos_listagem_orcamento_fichafinanceira',
                                    angular.extend({}, {'atc': parameters.atc, 'fornecedor': parameters.fornecedor}, { 'offset': ''}), true),
                            });

                            // let { data: entity } = await this.$http({
                            //     method: 'GET',
                            //     url: this.nsjRouting.generate(
                            //         'crm_propostasitens_index',
                            //         angular.extend({}, this.entityService.constructors, { 'offset': '', 'fornecedor': parameters.fornecedor }), true),
                            // });

                            // //necessario para saber se permite orçamento zerado
                            // let promiseAtcArea = this.$http({
                            //     method: 'GET',
                            //     url: this.nsjRouting.generate(
                            //         'crm_atcsareas_get',
                            //         angular.extend({}, this.entityService.constructors, { 'offset': '', 'id': parameters.atcArea }), true),
                            // });

                            // //necessario para saber se permite orçamento zerado
                            // let promiseFornecedor = this.$http({
                            //     method: 'GET',
                            //     url: this.nsjRouting.generate(
                            //         'ns_fornecedores_get',
                            //         angular.extend({}, this.entityService.constructors, { 'offset': '', 'id': parameters.fornecedor }), true),
                            // });


                            primiseData = await Promise.all([promiseOrcamentos]);
                            
                            entity['listagemOrcamentos'] = primiseData[0].data;
                            // entity['atcArea'] = primiseData[1].data;
                            // entity['fornecedorObj'] = primiseData[2].data;
                            entity['atc'] = parameters.atc;
                            entity['proposta'] = parameters.proposta;
                            entity['fornecedor'] = parameters.fornecedor;
                            entity['fornecedorEnvolvido'] = parameters.fornecedorEnvolvido;

                            return entity;
                        } else {
                            let entity='';
                        //    subentity.pai = paiId;
                        //    return angular.copy(subentity);
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
