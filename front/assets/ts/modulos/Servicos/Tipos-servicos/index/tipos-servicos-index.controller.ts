import angular = require('angular');
import { TiposServicosService } from '../tipos-servicos.service';
import { TransitionService } from '@uirouter/core';
export class TiposServicosIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposServicosService',
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
        public tiposServicosService: TiposServicosService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService
    ) {
        tiposServicosService.filter = $stateParams.q ? $stateParams.q : '';
        tiposServicosService.filters = {};
        tiposServicosService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                tiposServicosService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'tiposervico') {
                tiposServicosService.constructors[i] = $stateParams[i];
            }
        }
        this.service = tiposServicosService;
        this.filters = tiposServicosService.filters;
        this.entities = tiposServicosService.reload();
        this.fields = $rootScope.FIELDS_Tiposservicos;
        $scope.$on('tiposservicos_deleted', (event: any) => {
            this.tiposServicosService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.tiposServicosService.loading_deferred) {
                this.tiposServicosService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('tiposservicos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.tiposServicosService.reload();
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
        let entities = this.tiposServicosService.search(filter);
        let filterURL = angular.copy(this.tiposServicosService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.tiposServicosService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.tiposServicosService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.tiposServicosService.loadParams.busy;
    }
}
