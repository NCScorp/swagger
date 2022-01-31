/**
 * Módulo contendo as classes utilizadas pelo componente NsjTreeGrid
 */
export module NsjTelaListaClasses {
    export class Config {
        /**
         * Configuração dos campos da listagem do tipo Tabela
         */
        configCamposListagemTabela: any[] = [];
        /**
         * Configuração dos campos da listagem do tipo BuiltIn
         */
        configCamposListagemBuiltIn: CampoListagemBuiltIn[] = [];
        /**
         * Ações da tabela
         */
        acoesTabela: AcaoTabela[] = [];
        /**
         * Ações do header
         */
        acoesHeader: AcaoHeader[] = [];
        /**
         * Lista de callbacks utilizados pela tela
         */
        callbacks: any = {};

        public rotaVisualizacao: string;
        public rotaCriacao: string;
        public rotaEdicao: string;

        constructor(
            /**
             * Define o tipo de tela de listagem
             */
            public tipoTela: ETipoTela = ETipoTela.ttBuiltIn,
            /**
             * Guarda configurações do título da tela
             */
            public tituloTela: ITituloTela,
            /**
             * Campo identificador da entidade
             */
            public campoid: string | string[] = '',
            /**
             * Nome da entidade
             */
            public nomeEntidade: string = '',
            /**
             * Módulo da entidade
             */
            public moduloEntidade: string = ''
        ){
            this.configurarRotas();
        }

        /**
         * Configura as rotas padrões do sistema
         */
        configurarRotas(){
            this.rotaVisualizacao = `${this.nomeEntidade}_show`;
            this.rotaCriacao = `${this.nomeEntidade}_new`;
            this.rotaEdicao = `${this.nomeEntidade}_edit`;
        }

        /**
         * Retorna os parametros da rota de criação
         */
        getParamsRotaCriacao(){
            let params = {};

            return params;
        }

        /**
         * Retorna os parametros da rota de edição
         * @param entity 
         */
        getParamsRotaEdicao(entity: any){
            let params = {};
            params[<string>this.campoid] = entity[<string>this.campoid];

            return params;
        }

        /**
         * Retorna os parametros da rota de visualização
         * @param entity 
         */
        getParamsRotaVisualizacao(entity: any){
            let params = {};
            params[<string>this.campoid] = entity[<string>this.campoid];

            return params;
        }

        /**
         * Retorna o campo principal da listagem
         */
        getCampoListaBuiltInPrincipal(){
            return this.configCamposListagemBuiltIn.find((campoFind) => {
                return campoFind.tipo == ECampoListagemBuiltInTipo.clbitPrincipal;
            });
        }
    }

    export interface ITituloTela {
        singular: string;
        plural?: string;
        artigoIndefinidoSingular: string;
    }

    export class AcaoTabela {
        /**
         * Target da ação. Default "_self", abrir rota na mesma tela
         */
        public target: '_self' | '_blank' = '_self';

        constructor(
            /**
             * Label da ação.
             */
            public nome: string,
            /**
             * Nome do ícone a ser utilizado.
             */
            public icone: string,
            /**
             * Função chamada quando a ação for clicada
             */
            public onClick: (entity: any) => void,
            /**
             * Função chamada para verificar se a ação deve estar visível
             */
            public isVisible?: (entity: any) => boolean
        ) {
            // Caso não tenha sido declarada, defino uma função padrão
            if (!isVisible) {
                this.isVisible = (entity: any) => {
                    return true;
                };
            }
        }
    }

    export class AcaoHeader {
        /**
         * Target da ação. Default "_self", abrir rota na mesma tela
         */
        public target: '_self' | '_blank' = '_self';

        constructor(
            /**
             * Label da ação.
             */
            public nome: string,
            /**
             * Nome do ícone a ser utilizado.
             */
            public icone: string,
            /**
             * Função chamada quando a ação for clicada
             */
            public onClick: () => void,
            /**
             * Função chamada para verificar se a ação deve estar visível
             */
            public isVisible?: () => boolean
        ) {
            // Caso não tenha sido declarada, defino uma função padrão
            if (!isVisible) {
                this.isVisible = () => {
                    return true;
                };
            }
        }
    }

    export class CampoListagemBuiltIn {
        constructor(
            /**
             * Nome da propriedade do campo
             */
            public nome: string,
            /**
             * Título a ser apresentado para o campo
             */
            public titulo: string,
            /**
             * Tipo do campo da listagem
             */
            public tipo: ECampoListagemBuiltInTipo = ECampoListagemBuiltInTipo.clbitValor,
            /**
             * Função chamada quando o item for clicada
             */
            public getValor?: (entity: any) => void
        ) {
            // Caso não tenha sido declarada, defino uma função padrão
            if (!getValor) {
                this.getValor = (entity: any) => {
                    return entity[this.nome];
                };
            }
        }
    }

    /**
     * Enum referente aos tipos de tela de listagem
     */
    export enum ETipoTela {
        ttTabela,
        ttBuiltIn
    }

    /**
     * Enum referente aos tipos de campo da listagem built-in
     */
    export enum ECampoListagemBuiltInTipo {
        clbitPrincipal,
        clbitValor
    }
}