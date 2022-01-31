import angular = require('angular');

export class NsDocumentosFopTemplatesService {
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
    public rotaErpApi: boolean = true;

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
        delete this.filters['documentotemplate'];
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
                url: this.nsjRouting.generate('ns_documentosfoptemplates_index', angular.extend({}, constructors, {'offset': offset, 'filter': search}, filters ? filters : this.filters), true, this.rotaErpApi),
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
                                                this.after.created_at = this.entities[this.entities.length - 1].created_at;
                        this.after.documentotemplate = this.entities[this.entities.length - 1].documentotemplate;
                                            }
                                        if (data.length < 20) {
                                            this.loadParams.finished = true;
                        this.$rootScope.$broadcast('ns_documentosfoptemplates_list_finished', this.entities);
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
                    if ( this.loaded || this.loadParams.finished ) {
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
            if (this.entities[i].documentotemplate === identifier) {
                return this.entities[i];
            }
        }
        return null;
    }

    delete($identifier: any, force: boolean) {
        if (typeof($identifier) === 'object') {
            $identifier = $identifier.documentotemplate;
        }
        if ($identifier) {
            if (force ) {
                this.loading_deferred = this.$q.defer();
                this.$http
                                .delete(this.nsjRouting.generate('ns_documentosfoptemplates_delete', angular.extend(this.constructors, {'id': $identifier }), true, this.rotaErpApi))
                .then((response: any) => {
                    this.$rootScope.$broadcast('ns_documentosfoptemplates_deleted', {
                        entity: this.entity,
                        response : response
                    });
                })
                .catch((response: any) => {
                    this.$rootScope.$broadcast('ns_documentosfoptemplates_delete_error', {
                        entity: this.entity,
                        response : response
                    });
                });
            }
        }
    }

    _save(entity: any, autosave: boolean) {
        let method, url;
        if (entity.documentotemplate) {
            method = 'PUT';
            url = this.nsjRouting.generate('ns_documentosfoptemplates_put', {  'id': entity.documentotemplate }, true, this.rotaErpApi);
        } else {
            method = 'POST';
            url = this.nsjRouting.generate('ns_documentosfoptemplates_create', angular.extend(this.constructors), true, this.rotaErpApi);
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
                if (response.data.documentotemplate) {
                    entity.documentotemplate = response.data.documentotemplate;
                }
                entity.$$_saved = true;
                this.$rootScope.$broadcast('ns_documentosfoptemplates_submitted', {
                    entity: entity,
                    response : response,
                    autosave : autosave
                });
            })
            .catch((response: any) => {
                this.$rootScope.$broadcast('ns_documentosfoptemplates_submit_error', {
                    entity: entity,
                    response : response,
                    autosave : autosave
                });
            });
    }
    get(identifier : string) {
                
        return this.$q((resolve: any, reject: any) => {
            this.$http
            .get(this.nsjRouting.generate('ns_documentosfoptemplates_get', {  'id': identifier }, true, this.rotaErpApi))
            .then((response: any) => {
                this.$rootScope.$broadcast('ns_documentosfoptemplates_loaded', response.data);
                resolve(response.data);
            })
            .catch((response: any) => {
                reject(response);
            });
        });


    }
}
