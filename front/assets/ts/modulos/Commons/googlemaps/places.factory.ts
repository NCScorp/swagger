import angular = require('angular');

export class GooglePlacesService {

    static $inject = ['$http', 'nsjRouting', '$rootScope', '$q'];

    entities: any = [];
    loaded: boolean = false;
    constructors: any = {};
    after: any = {};
    filters: any = {};
    loadParams: any = { to_load: 3, busy: false, finished: false };
    loading_deferred: any = null;
    filter: any = '';

    constructor(private $http: any,
        private nsjRouting: any,
        private $rootScope: any,
        private $q: any) { }



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

        delete this.filters['ordemservico'];

        return this.load();
    }

    search(filter: any) {

        let filters = {};
        let search = '';

        if (typeof filter != 'undefined' && filter != null) {
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
                            (typeof (filter.filters[fil][pos]) === 'object' && filter.filters[fil][pos].value === undefined)) {
                            delete filter.filters[fil][pos];
                        }
                    }
                    filters[fil] = filter.filters[fil];
                } else if (typeof (filter.filters[fil]) === 'object' && filter.filters[fil].value !== undefined) {
                    filters[fil] = filter.filters[fil];
                }
            }

        } else if (filter == null) {
            return this._load(this.constructors, {}, '');
        }

        if (filter.lookup) {
            return this._load(this.constructors, {}, search);
        } else {
            this.filter = search;
            this.filters = filters;
            return this.reload();
        }
    };


    _load(constructors: any, offset: any, filter: any) {
        this.loading_deferred = this.$q.defer();

        return this.$q((resolve: any, reject: any) => {

            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('ns_enderecos_index_google_places', angular.extend({}, constructors, {
                    'offset': offset,
                    'filter': filter
                }, this.filters), true),
                timeout: this.loading_deferred.promise
            }).then((response: any) => {
                resolve(response.data);
            }).catch( (response: any) => {
                reject(response);
            });

        });

    };

    load() {

        if (!this.loadParams.busy && !this.loadParams.finished && this.loadParams.to_load > 0) {

            this.loadParams.busy = true;

            this._load(this.constructors, this.after, this.filter)
                .then( (data: any) => {
                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            this.entities.push(data[i]);
                        }
                    }
                    this.loadParams.finished = true;
                    this.$rootScope.$broadcast("googleplaces_loaded", this.entities);
                    this.loaded = true;

                    this.loadParams.to_load--;
                })
                .catch( (error: any)=> {
                    if (error.xhrStatus !== 'abort') {
                        this.loadParams.finished = true;
                    }
                })
                .finally( () => {
                    if (this.loaded || this.loadParams.finished) {
                        this.loadParams.busy = false;
                    }
                });
        }

        return this.entities;
    }

    loadMore() {
        this.loadParams.to_load = 3;
        this.load();
    };

    get(identifier : string) {
        this.loading_deferred = this.$q.defer();

        return this.$q((resolve: any, reject: any) => {
            this.$http
            .get(this.nsjRouting.generate('ns_enderecos_get_place', {  'id': identifier }, true),
            { timeout : this.loading_deferred.promise })
            .then((response: any) => {
                resolve(response.data);
            })
            .catch((response: any) => {
                reject(response);
            });
        });
    }

}
