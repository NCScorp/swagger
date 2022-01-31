import angular = require('angular');
import { TecnicosLogoService } from '../tecnicos-logo.service';

export class TecnicosLogoIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tecnicosLogoService',
        'toaster',
        '$rootScope',
        '$location',
        'nsjRouting'
    ];

    public busy: boolean = false;
    public entities: any;
    public fields: any;
    public service: any;
    public filters: any;
    public selected: any = [];
    public selectPage: boolean = false;
    public selectAll: boolean = false;
    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public tecnicosLogoService: TecnicosLogoService,
        public toaster: any,
        public $rootScope: any,
        public $location: any,
        public nsjRouting: any
    ) {
        tecnicosLogoService.filter = $stateParams.q ? $stateParams.q : '';
        tecnicosLogoService.filters = {};
        tecnicosLogoService.constructors = {};
        for (let i in $stateParams) {
            if (['ativo',].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                tecnicosLogoService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'tecnicologo') {
                tecnicosLogoService.constructors[i] = $stateParams[i];
            }
        }
        this.service = tecnicosLogoService;
        this.filters = tecnicosLogoService.filters;
        this.entities = tecnicosLogoService.reload();
        this.fields = $rootScope.FIELDS_TecnicosTecnicoslogos;
        $scope.$on('tecnicos_tecnicoslogos_deleted', (event: any) => {
            this.tecnicosLogoService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.tecnicosLogoService.loading_deferred) {
                this.tecnicosLogoService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('tecnicos_tecnicoslogos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.tecnicosLogoService.reload();
            }
        });
    }

    search(filter: any) {
        let entities = this.tecnicosLogoService.search(filter);
        let filterURL = angular.copy(this.tecnicosLogoService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.tecnicosLogoService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.tecnicosLogoService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.tecnicosLogoService.loadParams.busy;
    }
}
