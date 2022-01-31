import angular = require('angular');
import { TiposOrdensServicosService } from '../tipos-ordens-servicos.service';
import { TransitionService } from '@uirouter/core';

export class TiposOrdensServicosIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposOrdensServicosService',
        'toaster',
        '$rootScope',
        '$location',
        'nsjRouting',
        '$transitions',
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
        public tiposOrdensServicosService: TiposOrdensServicosService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService,
    ) {
        tiposOrdensServicosService.filter = $stateParams.q ? $stateParams.q : '';
        tiposOrdensServicosService.filters = {};
        tiposOrdensServicosService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                tiposOrdensServicosService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'tipoordemservico') {
                tiposOrdensServicosService.constructors[i] = $stateParams[i];
            }
        }
        this.service = tiposOrdensServicosService;
        this.filters = tiposOrdensServicosService.filters;
        this.entities = tiposOrdensServicosService.reload();
        this.fields = $rootScope.FIELDS_Tiposordensservicos;
        $scope.$on('tiposordensservicos_deleted', (event: any) => {
            this.tiposOrdensServicosService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.tiposOrdensServicosService.loading_deferred) {
                this.tiposOrdensServicosService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('tiposordensservicos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.tiposOrdensServicosService.reload();
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
        let entities = this.tiposOrdensServicosService.search(filter);
        let filterURL = angular.copy(this.tiposOrdensServicosService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.tiposOrdensServicosService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.tiposOrdensServicosService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.tiposOrdensServicosService.loadParams.busy;
    }
}
