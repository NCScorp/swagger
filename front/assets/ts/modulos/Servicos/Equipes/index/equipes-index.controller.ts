import angular = require('angular');
import { TransitionService } from '@uirouter/core'
import { EquipesService } from '../equipes.service';

export class EquipesIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'equipesService',
        'toaster',
        '$rootScope',
        '$location',
        'nsjRouting',
        '$transitions'
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
        public equipesService: EquipesService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService
    ) {

        equipesService.filter = $stateParams.q ? $stateParams.q : '';
        equipesService.filters = {};
        equipesService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                equipesService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'equipetecnico') {
                equipesService.constructors[i] = $stateParams[i];
            }
        }
        this.service = equipesService;
        this.filters = equipesService.filters;
        this.entities = equipesService.reload();
        this.fields = $rootScope.FIELDS_Equipes;
        $scope.$on('equipes_deleted', (event: any) => {
            this.equipesService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.equipesService.loading_deferred) {
                this.equipesService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('equipes_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.equipesService.reload();
            }
        });

        $transitions.onStart({}, () => {
            this.busy = true;
        });

        $transitions.onSuccess({}, () => {
            this.busy = false
        });

        $transitions.onError({}, () => {
            this.busy = false
        });
    }

    search(filter: any) {
        let entities = this.equipesService.search(filter);
        let filterURL = angular.copy(this.equipesService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.equipesService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.equipesService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.equipesService.loadParams.busy;
    }
}

