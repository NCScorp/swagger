import { any } from "core-js/fn/promise";
import { NsjTreeGridClasses, TreeGridClasses } from "./nsjtreegrid.classes";

export class NsjTreeGridController {

    static $inject = ['$scope'];

    //Funções de callback passadas para o componente
    public config: NsjTreeGridClasses.Config;

    //Objeto com dados utilizados pelo componente treeGrid
    public dadosTree = {
        arrDados: [],
        arrColunas: [],
        iconExpand: '',
        iconCollapse: '',
        expandingProperty: '',
        treeControl: {},
        expandLevel: 0
    }

    constructor (
        public $scope: any
    ) {}

    $onInit() {
        this.dadosTree = this.montarDadosTreeGrid( this.config );
        this.config.setRowActions();

        setTimeout(() => {
            this.expandirNiveis();
        });

        this.$scope.$watch('$ctrl.config.arrRows', (listaOld, listaNew) => {
            if (listaOld.length != listaNew.length || (
                !listaOld.every((itemVelho) => listaNew.some((itemNovo) => itemNovo.id == itemVelho.id)) 
            )) {
                this.dadosTree.arrDados = TreeGridClasses.TreeGridOrganizer.reorganizarNos(this.config.arrRows, 'id', 'idParent', 'children');
                this.config.setRowActions();
                this.expandirNiveis();
            }
        }, true);
    }

    /**
     * Expande itens da tree de acordo com configuração de expand level
     */
    expandirNiveis(){
        this.expandirSubNiveis(this.dadosTree.arrDados, this.config.expandLevel);
        this.reloadScope();
    }

    /**
     * Expande filhos ate chegar ao nível 1
     * @param dados 
     * @param nivel 
     * @returns 
     */
    expandirSubNiveis(dados, nivel = 1) {
        if (nivel < 2) {
            return;
        }
        dados.forEach((dado) => {
            this.expandirSubNiveis(dado.children, nivel -1);
            dado.expanded = true;
        })
    }

    private reloadScope(){
        this.$scope.$applyAsync();
    }

    private montarDadosTreeGrid(nsjTreeGridConfig: NsjTreeGridClasses.Config): any {
        let dadosTree: any = {
            arrDados: [],
            arrColunas: [],
            iconExpand: nsjTreeGridConfig.iconExpand,
            iconCollapse: nsjTreeGridConfig.iconCollapse,
            expandLevel: 1,
            treeControl: nsjTreeGridConfig.treeControl
        }
        
        // Monto array de definição de colunas
        nsjTreeGridConfig.arrColsConfig.forEach((colConfig, indexCol) => {
            let coluna: any = {
                field: colConfig.id,
                displayName: colConfig.nome,
                cellTemplate: this.getTemplate('default', {colConfig: colConfig}),
                cellTemplateScope: colConfig.cellTemplateScope,
                colConfigObject: colConfig
            }

            //Se for a primeira coluna, altero para template de coluna de expansão
            if (indexCol == 0) {
                coluna.cellTemplate = this.getTemplate('default-expand-column', {colConfig: colConfig});
            }
            else {
                if (colConfig.tipo == NsjTreeGridClasses.EnumColTipo.ectInput) {
                    coluna.cellTemplate = this.getTemplate('input', {colConfig: colConfig});
                }
            }

            //Monto cell template de acordo com o tipo
            switch (colConfig.tipo) {
                case NsjTreeGridClasses.EnumColTipo.ectHtml: {
                    coluna.cellTemplate = colConfig.cellTemplate;
                    break;
                }

                case NsjTreeGridClasses.EnumColTipo.ectBotoes: {
                    coluna.cellTemplate = this.getTemplate('botoes', {});
                    break;
                }

                case NsjTreeGridClasses.EnumColTipo.ectLabel: {
                    coluna.cellTemplate = this.getTemplate('label', {colConfig: colConfig});
                    break;
                }

                default:
                    break;
            }

            dadosTree.arrColunas.push(coluna);
        });

        //Preparo children dos dados baseado no id
        dadosTree.arrDados = TreeGridClasses.TreeGridOrganizer.reorganizarNos(nsjTreeGridConfig.arrRows, 'id', 'idParent', 'children');

        // Retorno dados
        return dadosTree;
    }

    private getTemplate(tipo: string, dados: any): string {

        //Template default
        let templateDefault = `
            <div class="icons-left"></div>
            <div class="col-valor">
                {{ col.colConfigObject.formatador.getFormatado( row.branch.get('${dados.colConfig.id}'), row.branch, col.colConfigObject ) }}
            </div>
            <div class="icons-right"></div> 
        `;

        switch (tipo) {
            case 'default-expand-column': {
                return `
                    <div class="icons-left">
                        <a ng-click="user_clicks_branch(row.branch)" class="icon-expand-colapse" ng-if="row.branch.children.length > 0">
                            <i ng-class="row.tree_icon" ng-click="row.branch.expanded = !row.branch.expanded" class="indented tree-icon"></i>
                        </a>
                        <a class="icon-config icon-left {{icone.id}}" ng-repeat="icone in col.colConfigObject.getIconsLeft(row.branch)" ng-if="icone.isVisible(row.branch)">
                            <i class="{{icone.nome}}"></i>
                        </a>
                    </div>
                    <div class="col-valor">
                        {{ col.colConfigObject.formatador.getFormatado( row.branch.get('${dados.colConfig.id}'), row.branch, col.colConfigObject ) }}
                    </div>
                `;
            }

            case 'botoes': {
                return `
                    <div class="botoes">
                        <div class="col-botao {{btn.id}}" ng-repeat="btn in row.branch.getColuna(col.colConfigObject.id).arrBotoes">
                            <nsj-button icon="{{btn.iconName}}" ng-click="btn.fnClick(row.branch)" ng-disabled="btn.isDisabled(row.branch)"
                                customClass="{{btn.classes}}">
                                {{btn.nome}}
                            </nsj-button>
                        </div>
                    </div>
                `;
            }

            case 'input': {
                let arrDadosInput: {nome: string, valor: string}[] = [];
                arrDadosInput.push({nome: 'type', valor: dados.colConfig.colConfigInput.tipo});
                arrDadosInput.push({nome: 'name', valor: dados.colConfig.id});
                arrDadosInput.push({nome: 'class', valor: "form-control"});
                arrDadosInput.push({nome: 'ng-model', valor: `row.branch.dados['${dados.colConfig.id}']`});

                if (dados.colConfig.colConfigInput.fnChange){
                    arrDadosInput.push({nome: 'ng-change', valor: "col.colConfigObject.colConfigInput.fnChange(row.branch, col.colConfigObject)"});
                }

                if (dados.colConfig.isDisabled){
                    arrDadosInput.push({nome: 'ng-disabled', valor: "col.colConfigObject.isDisabled(row.branch, row)"});
                }

                if (dados.colConfig.colConfigInput.min) {
                    arrDadosInput.push({nome: 'min', valor: dados.colConfig.colConfigInput.min});
                }

                if (dados.colConfig.colConfigInput.max) {
                    arrDadosInput.push({nome: 'max', valor: dados.colConfig.colConfigInput.max});
                }
                
                let inputText = "<input";

                arrDadosInput.forEach(dado => {
                    inputText += ` ${dado.nome}="${dado.valor}"`;
                })
                inputText += " />";

                // Se o campo estiver desabilitado, eu apresento o template default
                if (dados.colConfig.colConfigInput.showDefaultOnDisabled) {
                    return `
                        <div class="col-valor" ng-if="!col.colConfigObject.isDisabled(row.branch, row)">
                            ${inputText}
                        </div>
                        <div ng-if="col.colConfigObject.isDisabled(row.branch, row)">
                            ${templateDefault}
                        </div>
                    `;
                }
                else {
                    return `
                        <div class="col-valor">
                            ${inputText}
                        </div>
                    `;
                }
            }
        
            case 'label': {
                return `
                    <label class="label {{ col.colConfigObject.colConfigLabel.getLabelClass(row.branch, col.colConfigObject) }}">
                        {{ col.colConfigObject.formatador.getFormatado( row.branch.get('${dados.colConfig.id}'), row.branch, col.colConfigObject ) }}
                    </label>
                `
            }

            default: {
                return templateDefault
            }
        }
    }
}