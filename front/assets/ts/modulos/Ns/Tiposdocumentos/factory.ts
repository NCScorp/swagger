import angular = require('angular');

export class NsTiposdocumentos {
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
    novo : false /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
    

    constructor(public $http: angular.IHttpService, public nsjRouting: any, public $rootScope: angular.IScope, public $q: angular.IQService) {
    }

    
    reload() {
        if(this.loading_deferred){
            this.loading_deferred.resolve();
        }
    
        this.loadParams.finished = false;
        this.loadParams.to_load = 3;
        this.after = {};
        this.loaded = false;
        this.entities.length = 0;
        this.loadParams.busy = false;
        delete this.filters['tipodocumento'];
        /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
        delete this.constructors.tipodocumento;
        
        return this.load();
    }


    search(filter: any) {

        let search = '';
        
        //Se não receber filtro como parâmetro, atribuo filtro vazio a filters
        // Atribuição necessária porque ao sair de uma edição e fazer uma criação em tipo de documentos, o filtro usado na edição era mantido
        if(filter == null){
            this.filters = {};
        }
        
        if( typeof filter != 'undefined' && filter != null){
            
            search = filter.search ? filter.search : '' ;
            
            Object.keys(this.filters).forEach((k) => { 
                delete this.filters[k]
            });
            for( var fil in filter.filters ){
                if( fil.indexOf('.') > -1 ){
                    var filterSplit = fil.split('.');
                    this.filters[filterSplit[0]] = {};
                    this.filters[filterSplit[0]][filterSplit[1]] = filter.filters[fil];
                }
                else if( typeof(filter.filters[fil]) === 'object' && filter.filters[fil].hasOwnProperty('startDate') ){
                    this.filters[fil] = {inicio:{condition:'gte', value:filter.filters[fil].startDate},
                                         fim:{condition:'lte', value:filter.filters[fil].endDate}};
                }
                else{
                    this.filters[fil] = filter.filters[fil];
                }
            }
            
        }else if(filter == null){
            Object.keys(this.filters).forEach((k) => { delete this.filters[k]});
            /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
            if(this.entity.tipodocumento && !this.novo){
                this.filters['tipodocumento'] = {condition: 'neq', value: this.entity.tipodocumento}; 
            }
            /*--- */
            return this._load(this.constructors, {}, '');
        }
        
        if( filter.lookup )
        {
           
            return this._load(this.constructors, {}, search, this.filter);
        }else{
            this.filter = search;
            return this.reload();
        }
    }

    get(identifier : string) {
        this.loading_deferred = this.$q.defer();
        
        return this.$q((resolve:any, reject:any) => {
            this.$http.get(
                this.nsjRouting.generate('ns_tiposdocumentos_get', { 'id': identifier }, true, true),
                { timeout : this.loading_deferred.promise }
            )
            .then((response:any) => {
                this.$rootScope.$broadcast("ns_tiposdocumentos_loaded", response.data);
                this.entity = response.data; /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
                resolve(response.data);
            })
            .catch((response:any) => {
                reject(response);
            });
        });
    }

    _load(constructors: any, offset: any, search: string, filters?: any, useFilterPromise?: boolean) {
        this.loading_deferred = this.$q.defer();
        this.loading_deferred_filter = this.$q.defer();
        return this.$q((resolve: any, reject: any) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('ns_tiposdocumentos_index', angular.extend({}, constructors, { 'offset': offset, 'filter': search }, filters ? filters : this.filters), true, true),
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
                    }
                    this.loadParams.finished = true;
                    this.$rootScope.$broadcast('ns_tiposdocumentos_list_finished', this.entities);
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
            if (this.entities[i].tipodocumento === identifier) {
                return this.entities[i];
            }
        }
        return null;
    }

    delete($identifier: any, force: boolean) {
        if (typeof ($identifier) === 'object') {
            $identifier = $identifier.tipodocumento;
        }
        if ($identifier) {
            if (force) {
                this.loading_deferred = this.$q.defer();
                this.$http
                    .delete(this.nsjRouting.generate('ns_tiposdocumentos_delete', angular.extend(this.constructors, { 'id': $identifier }), true, true))
                    .then((response: any) => {
                        this.$rootScope.$broadcast('ns_tiposdocumentos_deleted', {
                            entity: this.entity,
                            response: response
                        });
                    })
                    .catch((response: any) => {
                        this.$rootScope.$broadcast('ns_tiposdocumentos_delete_error', {
                            entity: this.entity,
                            response: response
                        });
                    });
            }
        }
    }

    _save(entity: any, autosave: boolean) {
        let method, url;
        if (entity.tipodocumento) {
            method = 'PUT';
            url = this.nsjRouting.generate('ns_tiposdocumentos_put', { 'id': entity.tipodocumento }, true, true);
        } else {
            method = 'POST';
            url = this.nsjRouting.generate('ns_tiposdocumentos_create', angular.extend(this.constructors), true, true);
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
                if (response.data.tipodocumento) {
                    entity.tipodocumento = response.data.tipodocumento;
                }
                entity.$$_saved = true;
                this.$rootScope.$broadcast('ns_tiposdocumentos_submitted', {
                    entity: entity,
                    response: response,
                    autosave: autosave
                });
            })
            .catch((response: any) => {
                this.$rootScope.$broadcast('ns_tiposdocumentos_submit_error', {
                    entity: entity,
                    response: response,
                    autosave: autosave
                });
            });
    }
}

//function Tiposdocumentos($http, nsjRouting, $rootScope, $q) {
//
//    var this = {
//        entities: [],
//        loaded : false,        
//        constructors : {},
//        after : {},
//        filters: {},
//        novo : false, /* */
//        loadParams: { to_load : 3,
//                      busy : false,
//                      finished : false,},
//        loading_deferred: null,
//        filter: '',
//        reload : function(){
//            if(this.loading_deferred){
//                this.loading_deferred.resolve();
//            }
//        
//            this.loadParams.finished = false;
//            this.loadParams.to_load = 3;
//            this.after = {};
//            this.loaded = false;
//            this.entities.length = 0;
//            this.loadParams.busy = false;
//            delete this.filters['tipodocumento']; /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
//            delete this.constructors.tipodocumento;
//            
//            return this.load();
//        },
//        search : function (filter) {
//            if( typeof filter != 'undefined' && filter != null ){
//                this.filter = filter.search;
//                Object.keys(this.filters).forEach(function(k) { delete this.filters[k]});
//                for( var fil in filter.filters ){
//                    if( fil.indexOf('.') > -1 ){
//                        var filterSplit = fil.split('.');
//                        this.filters[filterSplit[0]] = {};
//                        this.filters[filterSplit[0]][filterSplit[1]] = filter.filters[fil];
//                    }
//                    else if( typeof(filter.filters[fil]) === 'object' && filter.filters[fil].hasOwnProperty('startDate') ){
//                        this.filters[fil] = {inicio:{condition:'gte', value:filter.filters[fil].startDate},
//                                             fim:{condition:'lte', value:filter.filters[fil].endDate}};
//                    }
//                    else{
//                        this.filters[fil] = filter.filters[fil];
//                    }
//                }
//                
//            }else if(filter == null){
//                Object.keys(this.filters).forEach(function(k) { delete this.filters[k]});
//                /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
//                if(this.entity && !this.novo){
//                  this.filters['tipodocumento'] = {condition: 'neq', value: this.entity.tipodocumento}; 
//                }
//                /*--- */
//                return this._load(this.constructors, {}, '');
//            }
//            
//            if( filter.lookup )
//            {
//               
//                return this._load(this.constructors, {}, this.filter);
//            }else{
//                return this.reload();
//            }
//        },
//        
//        _load: function (constructors, offset, filter) {
//            this.loading_deferred = $q.defer();
//
//            return $q(function (resolve, reject) {
//                $http({
//                    method: 'GET',
//                    url: nsjRouting.generate('tiposdocumentos_index', angular.extend({}, constructors, {'offset': offset, 'filter': filter}, this.filters), true),
//                    timeout: this.loading_deferred.promise
//                })
//                .then(function (response) {
//                     resolve(response.data);
//                 })
//                .catch(function (response) {
//                     reject(response);
//                });
//
//            });
//
//        },
//        load: function () {
//            if (!this.loadParams.busy && !this.loadParams.finished && this.loadParams.to_load > 0 ){
//                this.loadParams.busy = true;
//
//                this._load(this.constructors, this.after, this.filter)
//                    .then(function (data) {
//                        if (data.length > 0) {
//                            for (var i = 0; i < data.length; i++) {
//                                this.entities.push(data[i]);
//                            }
//                                                    }
//                                                    this.loadParams.finished = true;
//                            $rootScope.$broadcast("tiposdocumentos_list_finished", this.entities);
//                                                this.loaded = true;
//
//                        this.loadParams.to_load--;
//                    })
//                    .catch(function(error)
//                    {
//                        if(error.xhrStatus !== 'abort'){
//                            this.loadParams.finished = true;
//                        }
//                    })
//                    .finally(function(){
//                        if( this.loaded || this.loadParams.finished ){ 
//                            this.loadParams.busy = false;
//                        }
//                                            });
//            }
//
//            return this.entities;
//        },
//        loadMore: function () {
//            this.loadParams.to_load = 3;
//            this.load();
//        },
//        find:  function (identifier) {
//          for (var i in this.entities) {
//                if (this.entities[i]['tipodocumento'] == identifier) {
//                    return this.entities[i];
//                }
//            }
//            return null;
//        },
//                _save : function(entity, autosave){
//            var method, url;
//            if(entity['tipodocumento']){
//                method = "PUT";
//                url = nsjRouting.generate('tiposdocumentos_put',{  'id': entity['tipodocumento'] }, true);
//            }else{
//                method = "POST";
//                url = nsjRouting.generate('tiposdocumentos_create',angular.extend(this.constructors), true);
//            }
//            if(!autosave){
//                autosave = false;
//                entity['$$__submitting'] = true;
//            }
//            var data = angular.copy(entity);
//            if(data.hasOwnProperty('$$__submitting')){
//                delete data['$$__submitting'];
//            }
//            this.loading_deferred = $q.defer();
//            return $http({
//                method: method,
//                url: url,
//                data: data,
//                timeout: this.loading_deferred.promise
//                })
//            .finally(function(response){
//                if(!autosave){
//                    entity['$$__submitting'] = false;
//                }
//            });
//        },
//        save : function(entity, autosave){
//
//            this
//                ._save(entity, autosave)
//                .then(function (response) {
//                    if(response.data.tipodocumento){
//                        entity['tipodocumento'] = response.data.tipodocumento;
//                    }
//                    entity['$$_saved'] = true;
//                    $rootScope.$broadcast("tiposdocumentos_submitted", {
//                        entity: entity,
//                        response : response,
//                        autosave : autosave
//                    });
//                })
//                .catch(function (response) {
//                    $rootScope.$broadcast("tiposdocumentos_submit_error", {
//                        entity: entity,
//                        response : response,
//                        autosave : autosave
//                    });
//                });
//        },        
//        get : function(identifier){
//            this.loading_deferred = $q.defer();
//            
//            return $q(function(resolve, reject){        
//                $http
//                .get(nsjRouting.generate('tiposdocumentos_get', {  'id': identifier }, true),
//                { timeout : this.loading_deferred.promise })
//                .then(function (response) {
//                    $rootScope.$broadcast("tiposdocumentos_loaded", response.data);
//                    this.entity = response.data; /* Sobrescrito para não listar o próprio documento no lookup do objectlist */
//                    resolve(response.data);
//                })
//                .catch(function(response){
//                    reject(response);
//                });
//            });
//        }
//        
//        
//        
//        
//    };
//
//    return this;
//}

