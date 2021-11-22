import angular = require('angular');

import { EConfigRequisicaoTipo } from './interfaces/config-requisicao-tipo.enum';
import { IConfigRequisicao } from './interfaces/config-requisicao.interface';

export abstract class ServiceBase<E> {
    /**
     * Campo identificador da entidade
     */
    protected campoid: string = '';
    /**
     * Nome da entidade
     */
    protected nomeEntidade: string = '';
    /**
     * Módulo da entidade
     */
    protected moduloEntidade: string = '';
    /**
     * Lista de configurações de requisições de cada service
     */
    protected arrConfigRequisicoes: IConfigRequisicao[] = [];

    // VARIÁVEIS DO SERVICE PADRÃO - INICIO
    public entities: any[] = [];
    public loaded: boolean = false;
    public constructors: any = {};
    public after: any = '';
    public filters: any = {};
    public loading_deferred: any = null;
    public loading_deferred_filter: any = null;
    public filter: string = '';
    public entity: any = {};
    public loadParams: any = {
        to_load: 3,
        busy: false,
        finished: false
    };
    // VARIÁVEIS DO SERVICE PADRÃO - FIM

    /**
     * Configura o campo identificador da entidade
     */
    protected abstract setCampoid(): void;
    /**
     * Configura o nome da entidade
     */
    protected abstract setNomeEntidade(): void;
    /**
     * Configura o modulo da entidade
     */
    protected abstract setModuloEntidade(): void;

    constructor(
        protected $http: angular.IHttpService, 
        protected nsjRouting: any, 
        protected $rootScope: angular.IScope, 
        protected $q: angular.IQService
    ) {
        // Configuro informações gerais
        this.setCampoid();
        this.setNomeEntidade();
        this.setModuloEntidade();
        
        // Configuro requisições
        this.configurarRequisicoes();
    }

    public getCampoid(): string {
        return this.campoid;
    }

    /**
     * Dispara um evento de acordo com o nome e dados informados
     * @param nome 
     * @param dados 
     */
    protected dispararEvento(nome: string, dados: any = null){
        this.$rootScope.$broadcast(nome, dados);
    }

    /**
     * Gera url da api baseado no nome da rota e parametros passados
     * @param nome 
     * @param params 
     */
    protected gerarUrl(nome: string, params: any): string {
        let parametrosDestino = {};
        const url = this.nsjRouting.generate(nome, angular.extend(parametrosDestino, params), true);
        return url;
    }
    
    /**
     * Configura uma das requisições comuns presentes na maioria dos services
     * @param configuracao 
     */
    protected configurarRequisicao(configuracao: IConfigRequisicao){
        const indice = this.arrConfigRequisicoes.findIndex((configFindIndex) => {
            return configFindIndex.tipo == configuracao.tipo;
        });

        if (indice > -1) {
            this.arrConfigRequisicoes[indice] = configuracao;
        } else {
            this.arrConfigRequisicoes.push(configuracao);
        }
    }

    /**
     * Configura requisições padrões para cada service
     */
    protected configurarRequisicoes(): void {
        // Listar
        this.configurarRequisicao({
            tipo: EConfigRequisicaoTipo.crtListar,
            nomeRota: `${this.moduloEntidade}_${this.nomeEntidade}_index`,
            eventoSucesso: `${this.nomeEntidade}_list_finished`
        });
        // Buscar
        this.configurarRequisicao({
            tipo: EConfigRequisicaoTipo.crtBuscar,
            nomeRota: `${this.moduloEntidade}_${this.nomeEntidade}_get`,
            eventoSucesso: `${this.nomeEntidade}_loaded`
        });
        // Criar
        this.configurarRequisicao({
            tipo: EConfigRequisicaoTipo.crtCriar,
            nomeRota: `${this.moduloEntidade}_${this.nomeEntidade}_create`,
            eventoSucesso: `${this.nomeEntidade}_submitted`,
            eventoFalha: `${this.nomeEntidade}_submit_error`,
        });
        // Atualizar
        this.configurarRequisicao({
            tipo: EConfigRequisicaoTipo.crtAtualizar,
            nomeRota: `${this.moduloEntidade}_${this.nomeEntidade}_put`,
            eventoSucesso: `${this.nomeEntidade}_submitted`,
            eventoFalha: `${this.nomeEntidade}_submit_error`,
        });
        // Excluir
        this.configurarRequisicao({
            tipo: EConfigRequisicaoTipo.crtExcluir,
            nomeRota: `${this.moduloEntidade}_${this.nomeEntidade}_delete`,
            eventoSucesso: `${this.nomeEntidade}_deleted`,
            eventoFalha: `${this.nomeEntidade}_delete_error`,
        });
    }

    /**
     * Busca uma configuração de requisição por tipo
     * @param tipo 
     */
    public buscarConfiguracaoRequisicaoPorTipo(tipo: EConfigRequisicaoTipo): IConfigRequisicao {
        return this.arrConfigRequisicoes.find((configFindIndex) => {
            return configFindIndex.tipo == tipo;
        });
    }

    /**
     * Busca uma configuração de requisição por tipo
     * @param tipo 
     */
    public buscarConfiguracaoRequisicaoPorTipoCustomizado(tipoCustomizado: any): IConfigRequisicao {
        return this.arrConfigRequisicoes.find((configFindIndex) => {
            return configFindIndex.tipoCustomizado == tipoCustomizado
                && configFindIndex.tipo == EConfigRequisicaoTipo.crtCustom;
        });
    }

    /**
     * Realiza uma requisição post customizada e retorna a resposta de um dado tipo
     * @param tipoCustomizado 
     * @param construtores 
     * @param params 
     */
    public customPost<R>(tipoCustomizado: any, entidade: E, construtores: any = {}, params: any = {}): Promise<R>{
        // Buscco a configuração da requisição
        const config = this.buscarConfiguracaoRequisicaoPorTipoCustomizado(tipoCustomizado);

        // Caso a configuração não exista, disparo um erro
        if (config == null) {
            throw new Error('Requisição customizada não está configurada');
        }

        // Defino rota
        const url = this.gerarUrl(config.nomeRota, {...construtores, ...params});

        // Busco dados da requisição para
        return new Promise((resolve, reject) => {
            this.loading_deferred = this.$q.defer();
            this.$http({
                method: 'POST',
                url: url,
                data: entidade,
                timeout: this.loading_deferred.promise
            }).then((response: any) => {
                resolve(response.data);

                if (config.eventoSucesso) {
                    this.dispararEvento(config.eventoSucesso, response.data);
                }
            }).catch((response: any) => {
                reject(response);

                if (config.eventoFalha) {
                    this.dispararEvento(config.eventoFalha, response);
                }
            })
        });
    }

    /**
     * Dispara requisição de criar entidade
     * @param entidade 
     * @param construtores 
     */
    // public criar(entidade: E, construtores: any = {}): Promise<E> {
    //     // Buscco a configuração da requisição
    //     const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtCriar);

    //     // Caso a configuração não exista, disparo um erro
    //     if (config == null) {
    //         throw new Error('Requisição de criar não está configurada');
    //     }

    //     // Busco dados da requisição para
    //     return new Promise((resolve, reject) => {

    //     });
    // }

    /**
     * Dispara requisição de excluir entidade
     * @param entidade 
     * @param construtores 
     */
    public excluir(entidade: E, construtores: any = {}): Promise<void> {
        // Buscco a configuração da requisição
        const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtExcluir);

        // Caso a configuração não exista, disparo um erro
        if (config == null) {
            throw new Error('Requisição de exclusão não está configurada');
        }

        // Defino identificador
        const identificador = entidade[this.campoid];

        // Defino rota
        const url = this.gerarUrl(config.nomeRota, {
            ...construtores, 
            id: identificador
        });

        // Retorno promise em que faço a requisição e disparo eventos de sucesso ou erro.
        return new Promise((resolve, reject) => {
            this.loading_deferred = this.$q.defer();

            // Disparo requisição
            this.$http.delete(url, {timeout: this.loading_deferred.promise})
                .then((response: any) => {
                    resolve();
                    this.dispararEvento(config.eventoSucesso, {
                        entity: entidade,
                        response: response
                    });
                })
                .catch((response: any) => {
                    reject();
                    this.dispararEvento(config.eventoFalha, {
                        entity: entidade,
                        response: response
                    });
                });
        });
    }

    /**
     * Dispara requisição de buscar listagegm da entidade
     * @param entidade 
     * @param construtores 
     */
     public getAll(filters: any = {}, construtores: any = {}): Promise<E[]> {
        // Buscco a configuração da requisição
        const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtListar);

        // Caso a configuração não exista, disparo um erro
        if (config == null) {
            throw new Error('Requisição de listar não está configurada');
        }

        // Defino rota
        const url = this.gerarUrl(config.nomeRota, {
            ...construtores, 
            ...filters
        });

        // Retorno promise em que faço a requisição e disparo eventos de sucesso ou erro.
        return new Promise((resolve, reject) => {
            this.loading_deferred = this.$q.defer();

            // Disparo requisição
            this.$http.get(url, {timeout: this.loading_deferred.promise})
                .then((response: any) => {
                    resolve(response.data);
                    this.dispararEvento(config.eventoSucesso, response.data);
                })
                .catch((response: any) => {
                    reject();
                    this.dispararEvento(config.eventoFalha, {
                        response: response
                    });
                });
        });
    }

    // FUNÇÕES DO SERVICE PADRÃO - INICIO
    reload() {
        if (this.loading_deferred) {
            this.loading_deferred.resolve();
        }

        this.loadParams.finished = false;
        this.loadParams.to_load = 3;
        this.after = {};
        this.loaded = false;
        this.entities.length = 0;
        this.loadParams.busy = false;
        delete this.filters[this.campoid];
        return this.load();
    }

    search(filter: any) {
        let filters: any = {};
        let search = '';
        if (typeof (filter) !== 'undefined' && filter != null) {
            search = filter.search ? filter.search : '';
            for (let fil in filter.filters) {
                if (typeof (filter.filters[fil]) !== 'object') {
                    filters[fil] = filter.filters[fil];
                } else if (typeof (filter.filters[fil]) === 'object' && filter.filters[fil].hasOwnProperty('startDate')) {
                    filters[fil] = {
                        inicio: { condition: 'gte', value: filter.filters[fil].startDate },
                        fim: { condition: 'lte', value: filter.filters[fil].endDate }
                    };
                } else if (filter.filters[fil].constructor === Array) {
                    for (let pos in filter.filters[fil]) {
                        if (filter.filters[fil][pos] === undefined ||
                            (typeof (filter.filters[fil][pos]) === 'object' && filter.filters[fil][pos].value === undefined) ||
                            (typeof (filter.filters[fil][pos]) === 'object' && filter.filters[fil][pos].value === null)) {
                            delete filter.filters[fil][pos];
                        }
                    }
                    filters[fil] = filter.filters[fil];
                } else if (typeof (filter.filters[fil]) === 'object' && filter.filters[fil].value !== undefined && filter.filters[fil].value !== null) {
                    filters[fil] = filter.filters[fil];
                }
            }
        } else if (filter == null) {
            if (this.loading_deferred_filter) {
                this.loading_deferred_filter.resolve();
            }

            return this._load(this.constructors, {}, '', {}, true);
        }

        if (filter.lookup) {
            if (this.loading_deferred_filter) {
                this.loading_deferred_filter.resolve();
            }

            return this._load(this.constructors, {}, search, filters, true);
        } else {
            this.filter = search;
            this.filters = filters;
            return this.reload();
        }
    }

    _load(constructors: any, offset: any, search: string, filters?: any, useFilterPromise?: boolean) {
        // Busco configuração de rotas
        const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtListar);

        this.loading_deferred = this.$q.defer();
        this.loading_deferred_filter = this.$q.defer();
        return this.$q((resolve: any, reject: any) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate(config.nomeRota, angular.extend({}, constructors, { 'offset': offset, 'filter': search }, filters ? filters : this.filters), true),
                timeout: useFilterPromise ? this.loading_deferred_filter.promise : this.loading_deferred.promise
            })
                .then((response: any) => {
                    resolve(response.data);
                })
                .catch((response: any) => {
                    reject(response);
                });

        });

    }

    load() {
        // Busco configuração de rotas
        const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtListar);
        
        if (!this.loadParams.busy && !this.loadParams.finished && this.loadParams.to_load > 0) {
            this.loadParams.busy = true;

            this._load(this.constructors, this.after, this.filter)
                .then((data: any) => {
                    if (data.length > 0) {
                        for (let i = 0; i < data.length; i++) {
                            this.entities.push(data[i]);
                        }
                        this.after[this.campoid] = this.entities[this.entities.length - 1][this.campoid];
                        this.after[this.campoid] = this.entities[this.entities.length - 1][this.campoid];
                    }
                    if (data.length < 20) {
                        this.loadParams.finished = true;
                        this.dispararEvento(config.eventoSucesso, this.entities);
                    } else {
                        this.loadParams.finished = false;
                    }
                    this.loaded = true;

                    this.loadParams.to_load--;

                })
                .catch((error: any) => {
                    if (error.xhrStatus !== 'abort') {
                        this.loadParams.finished = true;
                    }
                })
                .finally(() => {
                    if (this.loaded || this.loadParams.finished) {
                        this.loadParams.busy = false;
                    }
                    this.load();
                });
        }

        return this.entities;
    }

    loadMore() {
        this.loadParams.to_load = 3;
        this.load();
    }

    find(identifier: string) {
        for (let i in this.entities) {
            if (this.entities[i][this.campoid] === identifier) {
                return this.entities[i];
            }
        }
        return null;
    }

    delete($identifier: any, force: boolean) {
        // Busco configuração de rotas
        const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtExcluir);

        if (typeof ($identifier) === 'object') {
            $identifier = $identifier[this.campoid];
        }
        if ($identifier) {
            if (force || confirm('Tem certeza que deseja deletar?')) {
                this.loading_deferred = this.$q.defer();
                this.$http.delete(
                    this.nsjRouting.generate(config.nomeRota, angular.extend(this.constructors, { 'id': $identifier }), true),
                        { timeout: this.loading_deferred.promise }
                ).then((response: any) => {
                    this.dispararEvento(config.eventoSucesso, {
                        entity: this.entity,
                        response: response
                    });
                }).catch((response: any) => {
                    this.dispararEvento('servicostecnicos', {
                        entity: this.entity,
                        response: response
                    });
                });
            }
        }
    }

    _save(entity: any, autosave: boolean = false) {
        let method, url;
        let config: IConfigRequisicao = null;

        if (entity[this.campoid]) {
            config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtAtualizar);
            method = 'PUT';
            url = this.gerarUrl(config.nomeRota, {
                id: entity[this.campoid]
            });
        } else {
            config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtCriar);
            method = 'POST';
            url = this.gerarUrl(config.nomeRota, this.constructors);
        }

        if (!autosave) {
            autosave = false;
            entity.$$__submitting = true;
        }
        let data = angular.copy(entity);
        if (data.hasOwnProperty('$$__submitting')) {
            delete data.$$__submitting;
        }
        this.loading_deferred = this.$q.defer();
        return this.$http({
            method: method,
            url: url,
            data: data,
            timeout: this.loading_deferred.promise
        }).finally(() => {
            if (!autosave) {
                entity.$$__submitting = false;
            }
        });
    }

    save(entity: any, autosave: boolean = false) {
        // Busco configuração de acordo com o tipo de requisição
        let config: IConfigRequisicao = null;

        if (entity[this.campoid]) {
            config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtAtualizar);
        } else {
            config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtCriar);
        }

        this._save(entity, autosave).then((response: any) => {
            if (response.data[this.campoid]) {
                entity[this.campoid] = response.data[this.campoid];
            }
            entity.$$_saved = true;
            this.dispararEvento(config.eventoSucesso, {
                entity: entity,
                response: response,
                autosave: autosave
            });
        })
        .catch((response: any) => {
            this.dispararEvento(config.eventoFalha, {
                entity: entity,
                response: response,
                autosave: autosave
            });
        });
    }

    /**
     * Busca entidade na api
     * @param identifier 
     * @returns 
     */
    get(identifier: string): Promise<E> {
        const config = this.buscarConfiguracaoRequisicaoPorTipo(EConfigRequisicaoTipo.crtBuscar);
        this.loading_deferred = this.$q.defer();

        return <Promise<E>>this.$q((resolve: any, reject: any) => {
            this.$http
                .get(this.nsjRouting.generate(config.nomeRota, { 'id': identifier }, true),
                    { timeout: this.loading_deferred.promise })
                .then((response: any) => {
                    this.dispararEvento(config.eventoSucesso, response.data);
                    resolve(response.data);
                })
                .catch((response: any) => {
                    reject(response);
                });
        });
    }
    // FUNÇÕES DO SERVICE PADRÃO - FIM
}