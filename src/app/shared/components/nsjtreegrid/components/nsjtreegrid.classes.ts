/**
 * Módulo contendo as classes utilizadas pelo componente NsjTreeGrid
 */
export module NsjTreeGridClasses {
    export class Config {
        /**
         * Icone de retração. Apresentado somente se a linha tiver filhos.
         */
        iconCollapse: string;
        /**
         * Icone de expansão. Apresentado somente se a linha tiver filhos.
         */
        iconExpand: string;
        /**
         * Level a partir do qual a tree deve ficar aberta
         */
        expandLevel: number = 1;
        /**
         * Configuração geral das colunas da tabela. 
         */
        arrColsConfig: ColConfig[] = [];
        /**
         * Linhas da tabela.
         */
        arrRows: Row[] = [];
        /**
         * Ações que serão atribuidas a cada linha da tree
         */
        arrAcoes: RowAction[] = [];

        treeControl: Partial<TreeControl> = {};

        /**
         * Preenche as ações de cada linha 
         */        
        public setRowActions(){
            this.arrRows.forEach((linha) => {
                linha.arrAcoes = this.arrAcoes;
            })
        }
    }

    /**
     * Configuração geral da coluna da tabela.
     */
    export class ColConfig {
        constructor(
            /**
             * Identificador da coluna. Não deve se repetir.
             */
            public id: string,
            /**
             * Título da coluna da tabela.
             */
            public nome: string,
            /**
             * Função chamada para verificar se a célula deve estar visivel
             */
            public isVisible?: (objLinha: Row, extraData: any) => boolean,
            /**
             * Função chamada para verificar se a célula deve estar desabilitado
             */
            public isDisabled?: (objLinha: Row, extraData: any) => boolean,
            /**
             * Define o tipo de dados que a coluna vai ter.
             */
            public tipo?: EnumColTipo,
            /**
             * Dados para montar o input quando o tipo for 'etcInput'
             */
            public colConfigInput?: ColConfigInput,
            /**
             * Define o alinhamento horizontal da coluna.
             */
            public alinhamentoH?: 'left' | 'right' | 'center',
            /**
             * Responsável por formatar o valor antes de ser impresso
             */
            public formatador?: IColConfigFormater,
            /**
             * Template html utilizado para desenhar a coluna. Utilizar quando os tipos forem 'ectHtml' / 'ectBotoes'.
             */
            public cellTemplate?: string,
            /**
             * Objeto com funções acessadas pelo template cellTemplate. Utilizar quando os tipos forem 'ectHtml' / 'ectBotoes'.
             */
            public cellTemplateScope?: any,
            /**
             * Icones que ficaram na coluna, do lado esquerdo ou direito da informação
             */
            public arrIcones: ColIcon[] = [],
            /**
             * Configuração de Coluna tipo label
             */
            public colConfigLabel?: ColConfigLabel
        ){
            if (!isVisible) {
                this.isVisible = (linha: Row, dadosExtra: any) => { return true; }
            }
            if (!isDisabled) {
                this.isDisabled = (linha: Row, dadosExtra: any) => { return true; }
            }
            if (!tipo) {
                this.tipo = EnumColTipo.ectValor
            }
            if (!alinhamentoH) {
                this.alinhamentoH = 'left';
            }
            if (!formatador) {
                this.formatador = new CustomColConfigFormater();
            }
        }

        /**
         * Retorna os icones da coluna.
         * @param linha 
         */
        public getIconsLeft(linha: Row): ColIcon[] {
            let arrIcons: ColIcon[] = this.arrIcones.filter((icone) => {
                return icone.lado == 'left'
            });
            

            return arrIcons;
        }
    }
    
    /**
     * Linha do grid
     */
    export class Row implements TreeGridClasses.ITreeNo {
        /**
         * Colunas da linha.
         */
        public arrCols: Col[] = [];
        /**
         * Acoes da linha.
         */
        public arrAcoes: RowAction[] = [];
        /**
         * Filhos da linha.
         */
        public children: Row[] = [];
        /**
         * Nível da linha
         */
        public level: Number;
        public pai: Row;

        constructor (
            /**
             * Identificador da linha. Será utilizado pelo 'idParent' de outras linhas para identificar as relações pai-filho.
             */
            public id: string,
            /**
             * Identificador da linha pai. No caso de não possuir pai, deixar vazio.
             */
            public idParent: string,
            /**
             * Dados da linha
             */
            public dados: any,
            /**
             * Id da coluna de con
             */
            arrConfigColunas: ColConfig[],
            /**
             * Classes customizadas para a linha
             */
            public classes: string = '',
        ) {
            //Crio Objetos de coluna da linha
            arrConfigColunas.forEach(colConfig => {
                let coluna = new Col(colConfig.id);
                this.arrCols.push(coluna);
            });
        }

        /**
         * Retorna o valor da coluna passada.
         * @param coluna 
         */
        get(coluna: string): any {
            let valor = this.dados[coluna];
            
            if (typeof(valor) === 'function'){
                return this.dados[coluna](this);
            }

            return valor;
        }

        /**
         * Seta o valor para a coluna passada
         * @param coluna 
         * @param valor 
         */
        set(coluna, valor: any): void {
            this.dados[coluna] = valor;
        }

        /**
         * Retorna a coluna de acordo com o identificador passado
         * @param coluna 
         */
        getColuna(coluna: string): Col {
            return this.arrCols.find(col => {return col.idColumnConfig == coluna})
        }

        /**
         * Atualiza coluna
         * @param coluna 
         */
        setColuna(coluna: Col) {
            const colIndex = this.arrCols.findIndex(col => col.idColumnConfig == coluna.idColumnConfig);
            this.arrCols[colIndex] = coluna;
        }

        /**
         * Será chamada pelo layout de Tree para apresentar, ou não, o bloco de acões na linha.
         */
        public blockAcoesIsVisible(): boolean {
            const possuiAcoes = this.arrAcoes.length > 0;
            const possuiAcoesVisiveis = possuiAcoes && this.arrAcoes.some((acao) => acao.isVisible(this));
    
            return possuiAcoes && possuiAcoesVisiveis;
        }
    }

    /**
     * Coluna das linhas do grid
     */
    export class Col {
        
        constructor (
            /**
             * Id referente a coluna de configuração
             */
            public idColumnConfig: string,
            /**
             * Icones apresentados do lado do valor da coluna
             */
            public arrIcons?: ColIcon[],
            /**
             * Botões da coluna. Utilizado quando o tipo da coluna for botões
             */
            public arrBotoes?: ColButton[]
        ) {
            if (!arrIcons) {
                this.arrIcons = [];
            }
            if (!arrBotoes) {
                this.arrBotoes = [];
            }
        }
    }

    /**
     * Icones apresentados do lado do valor da coluna
     */
    export class ColIcon {
        constructor(
            /**
             * Idenfificador do ícone. Será adicionada uma classe contendo esse identificador.
             */
            public id: string,
            /**
             * Nome do ícone a ser utilizado.
             */
            public nome: string,
            /**
             * De qual lado o ícone deve ficar em relação ao valor da coluna.
             */
            public lado: 'right' | 'left' = 'left',
            /**
             * Função chamada para verificar se o ícone deve estar visível
             */
            public isVisible?: (objLinha: Row) => boolean
        ) {
            if (!isVisible) {
                this.isVisible = (linha: Row) => { return true };
            }
        }
    }

    /**
     * Botões da coluna do grid
     */
    export class ColButton {
        constructor (
            /**
             * Identificador do botão.
             */
            public id: string,
            /**
             * Nome apresentado no botão
             */
            public nome: string,
            /**
             * Função chamada quando o botão for clicado
             */
            public fnClick: (objLinha: Row) => void,
            /**
             * Função chamada para verificar se o botão deve estar desabilitado
             */
            public isDisabled?: (objLinha: Row) => boolean,
            /**
             * Classes que o botão vai receber.
             */
            public classes: string = '',
            /**
             * Nome do ícone
             */
            public iconName: string = ''
        ) { 
            if (!isDisabled) {
                this.isDisabled = (linha: Row) => { return false };
            }
        }
    }

    export class RowAction {
        
        constructor(
            /**
             * Label da ação.
             */
            public nome: string,
            /**
             * Nome do ícone a ser utilizado.
             */
            public icon: string,
            /**
             * Função chamada quando a ação for clicada
             */
            public fnClick: (objLinha: Row) => void,
            /**
             * Função chamada para verificar se a ação deve estar visível
             */
            public isVisible?: (objLinha: Row) => boolean
        ) {
            if (!isVisible) {
                this.isVisible = (linha: Row) => { return true };
            }
        }
    }

    /**
     * Classe padrão de formatação de valor
     */
    export class CustomColConfigFormater implements IColConfigFormater {
        getFormatado(valor: any, linha: Row, colConfig: ColConfig): string {
            return valor;
        }
    }

    /**
     * Classe utilizada para definir o tipo de input da coluna.
     */
    export class ColConfigInput {

        constructor (
            public tipo: 'text' | 'number',
            public min?: number,
            public max?: number,
            public fnChange?: (objLinha: Row, col: ColConfig) => void,
            public showDefaultOnDisabled: boolean = true
        ) { }
    }

    /**
     * Classe utilizada para definir configurações para o tipo de coluna Label
    */
    export class ColConfigLabel {
        
        constructor (
            public getLabelClass: (objLinha: Row, col: ColConfig) => EColConfigLabelClass
        ) { }
    }

    export enum EColConfigLabelClass {
        cclcPrimary = 'label-primary',
        cclcInfo = 'label-info',
        cclcGreen = 'label-success',
        cclcYellow = 'label-warning',
        cclcRed = 'label-danger',
        cclcGrey = 'label-grey'
    }

    /**
     * Classe de formatação de valor number
     */
    export class ColConfigFormaterNumber implements IColConfigFormater {

        constructor(
            private casasDecimais: number = 2,
            private localeStringFormat: string = 'pt-BR',
            private preValorText: string = 'R$ '
        ) {}

        getFormatado(valor: any, linha: Row, colConfig: ColConfig): string {
            valor = parseFloat(valor.toString());

            const valorFixado = parseFloat(valor.toFixed(this.casasDecimais));
            let valorLocaleStr = valorFixado.toLocaleString(this.localeStringFormat);

            //Se o valor precisa ter casas decimais e o valor não tem, adiciono precisao ao final
            if (this.casasDecimais > 0 && valor.toString().indexOf('.') == -1){
                valorLocaleStr += ',';

                for (let index = 0; index < this.casasDecimais; index++) {
                    valorLocaleStr += '0';
                }
            }

            return (this.preValorText + valorLocaleStr);
        }
    }

    /**
     * Enum com os tipos de coluna da tabela.
     */
    export enum EnumColTipo {
        ectValor, ectHtml, ectBotoes, ectInput, ectLabel
    }

    /**
     * Interface utilizada para formatar o valor de um dado Tipo.
     */
    export interface IColConfigFormater {
        // getFormatado(valor: Tipo): string;
        getFormatado: IFnColConfigFormater;
    }

    //Funções de formatação
    export interface IFnColConfigFormater {
        (valor: any, linha: Row, colConfig: ColConfig): string
    }

    export interface TreeControl {
        collapse_all: Function
        collapse_branch: (row: NsjTreeGridClasses.Row) => void
        expand_all: Function
        expand_branch: (row: NsjTreeGridClasses.Row) => void
    }
}

/**
 * Módulo com funções para tratar os dados do TreeGrid
 */
export module TreeGridClasses {
    export interface ITreeNo {
        children: ITreeNo[];
        level: Number;
    }
    /**
     * Classe com funções para reorganizar os dados da TreeGrid
     */
    export class TreeGridOrganizer {
        
        /**
         * Esse método pega um array e reorganiza os dados em uma matriz que será consumida pela treeGrid.
         * @param arrDados 
         * @param idField 
         * @param parentField 
         */
        public static reorganizarNos(arrDados: NsjTreeGridClasses.Row[] = [], idField: string, parentField: string, childrenField){
            let arrDadosAux = arrDados.map((item) => {
                item[childrenField] = [];
                return item;
            });
          
            let arrDadosTree = [];
            
            arrDadosTree = TreeGridOrganizer.buscarFilhos(arrDadosAux, null, idField, parentField, childrenField);
            return arrDadosTree;
        }

        /**
         * Busca filhos do arrDadosAux, baseado nos campos de id e parentField.
         * @param arrDadosAux 
         * @param id
         * @param idField
         * @param parentField
         * @param childrenField
         */
        public static buscarFilhos(arrDadosAux: NsjTreeGridClasses.Row[] = [], idPai, idField, parentField, childrenField) {
            let arrChildren: NsjTreeGridClasses.Row[] = [];
            
            //Busco filhos
            arrChildren = arrDadosAux.filter(dado => dado[parentField] == idPai);
            //Para cada filho, busco seus filhos
            arrChildren.forEach((no) => {
                //Adiciono filhos ao nó
                no[childrenField] = TreeGridOrganizer.buscarFilhos(arrDadosAux, no[idField], idField, parentField, childrenField);
            });

            return arrChildren;
        }
    }
}