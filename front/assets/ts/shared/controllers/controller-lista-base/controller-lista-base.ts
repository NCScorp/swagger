import angular = require('angular');

import { EConfigRequisicaoTipo } from '../../services/service-base/interfaces/config-requisicao-tipo.enum';
import { ServiceBase } from '../../services/service-base/service-base.service';
import { NsjTelaListaClasses } from '../../components/nsjtelalista/components/nsjtelalista.classes';
import { TransitionService } from '@uirouter/core';

/**
 * Classe base para controllers de telas de listagem.
 *  - <T> representa a interface da entidade com a qual o controller e service trabalham
 */
export abstract class ControllerListaBase<T> {
    /**
     * Define se a tela está em processamento
     */
    public busy: boolean = false;
    /**
     * Configurações do componente de lista
     */
    public configLista: NsjTelaListaClasses.Config;
    /**
     * Entidades da tela
     */
    public entities: any[] = [];

    constructor(
        protected $scope: angular.IScope,
        protected $rootScope: angular.IScope,
        protected $state: angular.ui.IStateService,
        protected $stateParams: angular.ui.IStateParamsService,
        protected $location: angular.ILocationService,
        protected $transitions: TransitionService,
        protected service: ServiceBase<T>,
    ) {
        // Crio configuração de listagem
        this.criarConfiguracaoTela();

        // Configuro campos de listagem
        this.configurarCamposListagem();

        // Configuro callbacks
        this.configurarCallbacks();

        // Configuro ações do header
        this.configurarAcoesHeader();

        // Configuro ações da tabela 
        this.configurarAcoesTabela();

        // Configura parametros iniciais da tela
        this.configurarParametrosIniciais();

        // Configura evento de exclusão de entidade
        this.configurarEventoDestruicaoController();

        // Configura evento de exclusão de entidade
        this.configurarEventoExclusaoEntidade();

        // Configura evento de criação de entidade
        this.configurarEventoCriacaoEntidade();

        // Configura eventos de filtros
        this.configurarEventosFiltros();

        // Configura transações de tela
        this.configurarTransacoesTela();
        
        // Carrega dados da tela
        this.recarregarDados();
    }

    /**
     * Configurar campos da listagem da tabela ou built-in
     */
    protected abstract configurarCamposListagem(): void;
    
    /**
     * Cria a configuração da tela
     */
    protected abstract criarConfiguracaoTela(): void;

    /**
     * Configura callbacks do componente de lista
     */
    protected configurarCallbacks(): void {
        // Callback para verificar se a tela está em processamento
        this.configLista.callbacks.isBusy = () => {
            return this.busy || this.isBusy();
        }
        // Callback para verificar se existem filtros ativos
        this.configLista.callbacks.isFilterEmpty = () => {
            return this.isFilterEmpty();
        }
        // Callback para realizar a busca
        this.configLista.callbacks.search = (dados) => {
            this.search(dados);
        }
        // Callback para buscar mais dados
        this.configLista.callbacks.loadMore = () => {
            this.loadMore();
        }
        // Callback para próxima página, pode ser chamada no modelo built-in
        this.configLista.callbacks.nextPage = () => {
            this.nextPage();
        }
        // Callback para buscar parametros de rota de criação
        this.configLista.callbacks.getParamsRotaCriacao = () => {
            return this.configLista.getParamsRotaCriacao();
        }
        // Callback chamado quando um item de built-in for clicado
        this.configLista.callbacks.onListaItemBuiltInClick = (entity: T) => {
            this.$state.go(this.configLista.rotaVisualizacao, this.configLista.getParamsRotaVisualizacao(entity));
        }
        // Callback chamado para verificar se exibe botão de criação
        this.configLista.callbacks.exibirBtnCriacao = () => {
            return true;
        }
    }

    /**
     * Configura ações da tabela
     */
    protected configurarAcoesTabela(): void {
        // Ação de editar
        this.configLista.acoesTabela.push(
            new NsjTelaListaClasses.AcaoTabela(
                'Editar',
                'fas fa-edit',
                (entity: T) => {
                    this.$state.go(this.configLista.rotaEdicao, this.configLista.getParamsRotaEdicao(entity));
                },
            )
        );

        // Ação de visualizar
        this.configLista.acoesTabela.push(
            new NsjTelaListaClasses.AcaoTabela(
                'Visualizar',
                'fas fa-eye',
                (entity: T) => {
                    this.$state.go(this.configLista.rotaVisualizacao, this.configLista.getParamsRotaVisualizacao(entity));
                }
            )
        );
    }

    /**
     * Configura ações do header
     */
    protected configurarAcoesHeader(): void {
        // Ação de criar
        this.configLista.acoesHeader.push(
            new NsjTelaListaClasses.AcaoHeader(
                'Criar',
                'fas fa-plus',
                () => {
                    this.$state.go(this.configLista.rotaCriacao, this.configLista.getParamsRotaCriacao());
                },
                () => {
                    return this.configLista.callbacks.exibirBtnCriacao();
                }
            )
        );
    }

    /**
     * Busca mais dados para a tela
     */
    protected loadMore(): void{
        this.getService().loadMore();
    };

    /**
     * Chamada a próxima página. Não presente em nas paginas até agora, mas é necessário pelo modelo built-in
     */
    protected nextPage(): void {}

    /**
     * Retorna true caso não existam filtros ativos na tela
     */
    protected isFilterEmpty(): boolean {
        return true;
    }

    /**
     * Retorna o service principal da tela
     */
    protected getService(): ServiceBase<T> {
        return this.service;
    }

    /**
     * Retorna se a tela está em processamento
     */
    protected isBusy() {
        return this.getService().loadParams.busy;
    }

    /**
     * Função chamada antes do search
     */
    protected preSearch(filter: any): void{};

    /**
     * Realiza busca de dados da tela
     */
    protected search(filter: any) {
        this.preSearch(filter);

        // Configuro filters de lookup
        let arrFilterLookup = this.getParamStringLookup();

        if (filter.filterLookup == undefined) {
            filter.filterLookup = {};
        }
        arrFilterLookup.forEach((filterLookup) => {
            if (filter.filterLookup[filterLookup.nome] != undefined) {
                filter.filters[filterLookup.nome] = [
                    filter.filterLookup[filterLookup.nome][filterLookup.chave]
                ];
            } else {
                delete filter.filters[filterLookup.nome];
            }
        });

        let entities = this.getService().search(filter);
        let filterURL = angular.copy(this.getService().filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, {
                'q': this.getService().filter
            }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    /**
     * Configura parametros iniciais de rota da tela
     */
    protected configurarParametrosIniciais(): void {
        this.getService().filter = this.$stateParams.q ? this.$stateParams.q : '';
        this.getService().filters = {};
        this.getService().constructors = {};

        let arrParamFilters = this.getParamStringFilters();
        let arrFilterLookup = this.getParamStringLookup();
        let arrFilterLookupKeys = arrFilterLookup.map((filterLookupMap) => {
            return filterLookupMap.nome;
        });
        
        for (let i in this.$stateParams) {
            if (this.$stateParams[i] !== undefined) {
                if (arrParamFilters.indexOf(i) > -1) {
                    this.getService().filters[i] = this.$stateParams[i];
                } else if (arrFilterLookupKeys.indexOf(i) > -1 ) {
                    this.getService().filters[i] = [
                        this.$stateParams[i]
                    ];
                } else if (typeof this.$stateParams[i] !== 'function' && i !== 'q' && i !== this.configLista.campoid) {
                    this.getService().constructors[i] = this.$stateParams[i];
                }
            }
        }
    }

    /**
     * Retorna nome dos param strings da tela
     */
    protected getParamStringFilters(): string[] {
        return [];
    }

    /**
     * Retorna nome dos param strings da tela
     */
    protected getParamStringLookup(): {nome: string, chave: string}[] {
        return [];
    }

    /**
     * Chama busca de dados do service, sem passagem de filtros
     */
    protected recarregarDados(){
        this.entities = this.getService().reload();
    }

    /**
     * Configura eventos que ocorrem quando o controller é destruído
     */
    protected configurarEventoDestruicaoController(): void {
        this.$scope.$on('$destroy', () => {
            if (this.getService().loading_deferred) {
                this.getService().loading_deferred.resolve();
            }
        });
    }

    /**
     * Configura eventos que ocorrem quando a entidade é excluída
     */
    protected configurarEventoExclusaoEntidade(): void{
        // Busco configuração de exclusão
        const configReqExcluir = this.getService().buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtExcluir);

        // Observo evento de sucesso da exclusão
        this.$scope.$on(configReqExcluir.eventoSucesso, (event: any) => {
            this.getService().reload();
        });
    }

    /**
     * Configura eventos que ocorrem quando a entidade é criada
     */
    protected configurarEventoCriacaoEntidade(): void{
        // Busco configuração de criação
        const configReqCriacao = this.getService().buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtCriar);

        // Observo evento de sucesso da criação
        this.$rootScope.$on(configReqCriacao.eventoSucesso, (event: any, args: any) => {
            if (!args.autosave) {
                this.getService().reload();
            }
        });
    }

    /**
     * Configura eventos de filtros da tela
     */
    protected configurarEventosFiltros(): void{}

    /**
     * Configura o que ocorre enquanto acontecem transações de telas
     */
    protected configurarTransacoesTela(): void{
        this.$transitions.onStart({}, () => {
            this.busy = true;
        });

        this.$transitions.onSuccess({}, () => {
            this.busy = false
        });

        this.$transitions.onError({}, () => {
            this.busy = false
        });
    }
}