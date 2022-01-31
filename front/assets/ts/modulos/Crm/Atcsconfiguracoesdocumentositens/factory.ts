import angular = require('angular');

export class CrmAtcsconfiguracoesdocumentositens {
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
    public loadParams: any = { to_load : 3,
                               busy : false,
                               finished : false};

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
        delete this.filters['atcconfiguracaodocumentoitem'];
        return this.load();
    }

    search(filter: any) {
        let filters: any = {};
        let search = '';
        if ( typeof(filter) !== 'undefined' && filter != null ) {
            search = filter.search ? filter.search : '' ;
            for ( let fil in filter.filters ) {
                if (typeof(filter.filters[fil]) !== 'object') {
                    filters[fil] = filter.filters[fil];
                } else if ( typeof(filter.filters[fil]) === 'object' && filter.filters[fil].hasOwnProperty('startDate') ) {
                    filters[fil] = {inicio: {condition: 'gte', value: filter.filters[fil].startDate},
                                         fim: {condition: 'lte', value: filter.filters[fil].endDate}};
                } else if (filter.filters[fil].constructor === Array) {
                    for ( let pos in filter.filters[fil] ) {
                        if (filter.filters[fil][pos] === undefined ||
                            (typeof(filter.filters[fil][pos]) === 'object' && filter.filters[fil][pos].value === undefined) ||
                            (typeof(filter.filters[fil][pos]) === 'object' && filter.filters[fil][pos].value === null)) {
                            delete filter.filters[fil][pos];
                        }
                    }
                    filters[fil] = filter.filters[fil];
                } else if (typeof(filter.filters[fil]) === 'object' && filter.filters[fil].value !== undefined && filter.filters[fil].value !== null) {
                    filters[fil] = filter.filters[fil];
                }
            }
                    } else if (filter == null) {
            if (this.loading_deferred_filter) {
                this.loading_deferred_filter.resolve();
            }

            return this._load(this.constructors, {}, '', {}, true);
        }

        if ( filter.lookup ) {
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
                url: this.nsjRouting.generate('crm_atcsconfiguracoesdocumentositens_index', angular.extend({}, constructors, {'offset': offset, 'filter': search}, filters ? filters : this.filters), true),
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
        if (!this.loadParams.busy && !this.loadParams.finished && this.loadParams.to_load > 0 ) {
            this.loadParams.busy = true;

            this._load(this.constructors, this.after, this.filter)
                .then((data: any) => {
                    if (data.length > 0) {
                        for (let i = 0; i < data.length; i++) {
                            this.entities.push(data[i]);
                        }
                                            }
                                            this.loadParams.finished = true;
                        this.$rootScope.$broadcast('crm_atcsconfiguracoesdocumentositens_list_finished', this.entities);
                                        this.loaded = true;

                    this.loadParams.to_load--;

                })
                .catch((error: any) => {
                    if (error.xhrStatus !== 'abort') {
                       this.loadParams.finished = true;
                    }
                })
                .finally(() => {
                    if ( this.loaded || this.loadParams.finished ) {
                        this.loadParams.busy = false;
                    }
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
            if (this.entities[i].atcconfiguracaodocumentoitem === identifier) {
                return this.entities[i];
            }
        }
        return null;
    }

    delete($identifier: any, force: boolean) {
        if (typeof($identifier) === 'object') {
            $identifier = $identifier.atcconfiguracaodocumentoitem;
        }
        if ($identifier) {
            if (force  || confirm('Tem certeza que deseja deletar?') ) {
                this.loading_deferred = this.$q.defer();
                this.$http
                                .delete(this.nsjRouting.generate('crm_atcsconfiguracoesdocumentositens_delete', angular.extend(this.constructors, {'id': $identifier }), true))
                .then((response: any) => {
                    this.$rootScope.$broadcast('crm_atcsconfiguracoesdocumentositens_deleted', {
                        entity: this.entity,
                        response : response
                    });
                })
                .catch((response: any) => {
                    this.$rootScope.$broadcast('crm_atcsconfiguracoesdocumentositens_delete_error', {
                        entity: this.entity,
                        response : response
                    });
                });
            }
        }
    }

    _save(entity: any, autosave: boolean) {
        let method, url;
        if (entity.atcconfiguracaodocumentoitem) {
            method = 'PUT';
            url = this.nsjRouting.generate('crm_atcsconfiguracoesdocumentositens_put', { atcconfiguracaodocumento : entity.atcconfiguracaodocumento.atcconfiguracaodocumento,  'id': entity.atcconfiguracaodocumentoitem }, true);
        } else {
            method = 'POST';
            url = this.nsjRouting.generate('crm_atcsconfiguracoesdocumentositens_create', angular.extend(this.constructors), true);
        }
        if (!autosave) {
            autosave = false;
            entity.$$__submitting = true;
        }
        let data = angular.copy(entity);
        if (data.hasOwnProperty('$$__submitting')) {
            delete data.$$__submitting;
        }

                  
        return this.$http({
            method: method,
            url: url,
            data: data
            })
        .finally(() => {
            if (!autosave) {
                entity.$$__submitting = false;
            }
        });

        
    }

    save(entity: any, autosave: boolean) {

        this._save(entity, autosave)
            .then((response: any) => {
                if (response.data.atcconfiguracaodocumentoitem) {
                    entity.atcconfiguracaodocumentoitem = response.data.atcconfiguracaodocumentoitem;
                }
                entity.$$_saved = true;
                this.$rootScope.$broadcast('crm_atcsconfiguracoesdocumentositens_submitted', {
                    entity: entity,
                    response : response,
                    autosave : autosave
                });
            })
            .catch((response: any) => {
                this.$rootScope.$broadcast('crm_atcsconfiguracoesdocumentositens_submit_error', {
                    entity: entity,
                    response : response,
                    autosave : autosave
                });
            });
    }
    get(atcconfiguracaodocumento: any, identifier : string) {
                
        return this.$q((resolve: any, reject: any) => {
            this.$http
            .get(this.nsjRouting.generate('crm_atcsconfiguracoesdocumentositens_get', { 'atcconfiguracaodocumento': atcconfiguracaodocumento,  'id': identifier }, true))
            .then((response: any) => {
                this.$rootScope.$broadcast('crm_atcsconfiguracoesdocumentositens_loaded', response.data);
                resolve(response.data);
            })
            .catch((response: any) => {
                reject(response);
            });
        });


    }
}
