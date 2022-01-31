import angular = require('angular');
import { Object } from 'core-js';
import { EquipesTecnicosService } from '../equipes-tecnicos.service';

export class EquipesTecnicosIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'equipesTecnicosService',
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
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public equipesTecnicosService: EquipesTecnicosService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any
    ) {
        equipesTecnicosService.filter = $stateParams.q ? $stateParams.q : '';
        equipesTecnicosService.filters = {};
        equipesTecnicosService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                equipesTecnicosService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'equipetecnicotecnico') {
                equipesTecnicosService.constructors[i] = $stateParams[i];
            }
        }
        this.service = equipesTecnicosService;
        this.filters = equipesTecnicosService.filters;
        this.entities = equipesTecnicosService.reload();
        this.fields = $rootScope.FIELDS_EquipesEquipestecnicos;
        $scope.$on('equipes_equipestecnicos_deleted', (event: any) => {
            this.equipesTecnicosService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.equipesTecnicosService.loading_deferred) {
                this.equipesTecnicosService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('equipes_equipestecnicos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.equipesTecnicosService.reload();
            }
        });
    }

    search(filter: any) {
        let entities = this.equipesTecnicosService.search(filter);
        let filterURL = angular.copy(this.equipesTecnicosService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.equipesTecnicosService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.equipesTecnicosService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.equipesTecnicosService.loadParams.busy;
    }
}
