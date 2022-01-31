import angular = require('angular');
import { TransitionService } from '@uirouter/core';
import { ServicosTecnicosService } from '../servicos-tecnicos.service';

export class ServicosTecnicosIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'servicosTecnicosService',
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
        public servicosTecnicosService: ServicosTecnicosService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService,
    ) {
        servicosTecnicosService.filter = $stateParams.q ? $stateParams.q : '';
        servicosTecnicosService.filters = {};
        servicosTecnicosService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                servicosTecnicosService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'servicotecnico') {
                servicosTecnicosService.constructors[i] = $stateParams[i];
            }
        }
        this.service = servicosTecnicosService;
        this.filters = servicosTecnicosService.filters;
        this.entities = servicosTecnicosService.reload();
        this.fields = $rootScope.FIELDS_Servicostecnicos;
        $scope.$on('servicostecnicos_deleted', (event: any) => {
            this.servicosTecnicosService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.servicosTecnicosService.loading_deferred) {
                this.servicosTecnicosService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('servicostecnicos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.servicosTecnicosService.reload();
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
        let entities = this.servicosTecnicosService.search(filter);
        let filterURL = angular.copy(this.servicosTecnicosService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.servicosTecnicosService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.servicosTecnicosService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.servicosTecnicosService.loadParams.busy;
    }
}
