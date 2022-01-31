import angular = require('angular');

export class EstoqueVeiculosService {
    static $inject = ['$http', 'nsjRouting', '$rootScope', '$q'];
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
    public USE_ERP_API: boolean = true;

    constructor(public $http: angular.IHttpService, public nsjRouting: any, public $rootScope: angular.IScope, public $q: angular.IQService) {
    }

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
        delete this.filters['veiculo'];
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
        this.loading_deferred = this.$q.defer();
        this.loading_deferred_filter = this.$q.defer();
        return this.$q((resolve: any, reject: any) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('estoque_veiculos_index', angular.extend({}, constructors, { 'offset': offset, 'filter': search }, filters ? filters : this.filters), true, this.USE_ERP_API),
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
        if (!this.loadParams.busy && !this.loadParams.finished && this.loadParams.to_load > 0) {
            this.loadParams.busy = true;

            this._load(this.constructors, this.after, this.filter)
                .then((data: any) => {
                    if (data.length > 0) {
                        for (let i = 0; i < data.length; i++) {
                            this.entities.push(data[i]);
                        }
                        this.after.veiculo = this.entities[this.entities.length - 1].veiculo;
                        this.after.veiculo = this.entities[this.entities.length - 1].veiculo;
                    }
                    if (data.length < 20) {
                        this.loadParams.finished = true;
                        this.$rootScope.$broadcast('estoque_veiculos_list_finished', this.entities);
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
            if (this.entities[i].veiculo === identifier) {
                return this.entities[i];
            }
        }
        return null;
    }

    delete($identifier: any, force: boolean) {
        if (typeof ($identifier) === 'object') {
            $identifier = $identifier.veiculo;
        }
        if ($identifier) {
            if (force) {
                this.loading_deferred = this.$q.defer();
                this.$http
                    .delete(this.nsjRouting.generate('estoque_veiculos_delete', angular.extend(this.constructors, { 'id': $identifier }), true, this.USE_ERP_API),
                        { timeout: this.loading_deferred.promise })
                    .then((response: any) => {
                        this.$rootScope.$broadcast('estoque_veiculos_deleted', {
                            entity: this.entity,
                            response: response
                        });
                    })
                    .catch((response: any) => {
                        this.$rootScope.$broadcast('estoque_veiculos_delete_error', {
                            entity: this.entity,
                            response: response
                        });
                    });
            }
        }
    }

    _save(entity: any, autosave: boolean = false) {
        let method, url;
        if (entity.veiculo) {
            method = 'PUT';
            url = this.nsjRouting.generate('estoque_veiculos_put', { 'id': entity.veiculo }, true, this.USE_ERP_API);
        } else {
            method = 'POST';
            url = this.nsjRouting.generate('estoque_veiculos_create', angular.extend(this.constructors), true, this.USE_ERP_API);
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
        })
            .finally(() => {
                if (!autosave) {
                    entity.$$__submitting = false;
                }
            });
    }

    save(entity: any, autosave: boolean = false) {

        this._save(entity, autosave)
            .then((response: any) => {
                if (response.data.veiculo) {
                    entity.veiculo = response.data.veiculo;
                }
                entity.$$_saved = true;
                this.$rootScope.$broadcast('estoque_veiculos_submitted', {
                    entity: entity,
                    response: response,
                    autosave: autosave
                });
            })
            .catch((response: any) => {
                this.$rootScope.$broadcast('estoque_veiculos_submit_error', {
                    entity: entity,
                    response: response,
                    autosave: autosave
                });
            });
    }
    get(identifier: string) {
        this.loading_deferred = this.$q.defer();

        return this.$q((resolve: any, reject: any) => {
            this.$http
                .get(this.nsjRouting.generate('estoque_veiculos_get', { 'id': identifier }, true, this.USE_ERP_API),
                    { timeout: this.loading_deferred.promise })
                .then((response: any) => {
                    this.$rootScope.$broadcast('estoque_veiculos_loaded', response.data);
                    resolve(response.data);
                })
                .catch((response: any) => {
                    reject(response);
                });
        });
    }
}
