import angular = require('angular');
import { IContas } from './interfaces/contas.interface';

export class DiretorioContas {
  static $inject = ['$http', 'nsjRouting', '$rootScope', '$q'];
  public ERP_API: boolean = true;
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

  constructor(public $http: angular.IHttpService, public nsjRouting: any, public $rootScope: angular.IScope, public $q: angular.IQService) {
  }

  findAll(paramFilters): Promise<IContas[]> {
    return new Promise((resolve, reject) => {
      this.$http({
        method: 'GET',
        url: this.nsjRouting.generate('diretorio_contas_index', angular.extend({}, this.constructors, paramFilters), true, this.ERP_API),
      }).then((response: any) => {
        resolve(response.data);
      }).catch((erro: any) => {
        reject(erro);
      })
    });
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
      if (filter.filterLookup && filter.filterLookup.tiposatividadesfilter) {
        filters.tiposatividadesfilter = filter.filterLookup.tiposatividadesfilter.tipoatividade;
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
        url: this.nsjRouting.generate('diretorio_contas_index', angular.extend({}, constructors, { 'offset': offset, 'filter': search }, filters ? filters : this.filters), true, this.ERP_API),
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
          this.$rootScope.$broadcast('diretorio_contas_list_finished', this.entities);
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
}
