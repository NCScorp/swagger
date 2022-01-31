import angular = require('angular');
import { TransitionService } from '@uirouter/core';
import { TiposManutencoesService } from '../tipos-manutencoes.service';

export class TiposManutencoesIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposManutencoesService',
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
        public tiposManutencoesService: TiposManutencoesService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService ,
    ) {
        tiposManutencoesService.filter = $stateParams.q ? $stateParams.q : '';
        tiposManutencoesService.filters = {};
        tiposManutencoesService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                tiposManutencoesService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'tipomanutencao') {
                tiposManutencoesService.constructors[i] = $stateParams[i];
            }
        }
        this.service = tiposManutencoesService;
        this.filters = tiposManutencoesService.filters;
        this.entities = tiposManutencoesService.reload();
        this.fields = $rootScope.FIELDS_Tiposmanutencoes;
        $scope.$on('tiposmanutencoes_deleted', (event: any) => {
            this.tiposManutencoesService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.tiposManutencoesService.loading_deferred) {
                this.tiposManutencoesService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('tiposmanutencoes_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.tiposManutencoesService.reload();
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
        let entities = this.tiposManutencoesService.search(filter);
        let filterURL = angular.copy(this.tiposManutencoesService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.tiposManutencoesService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.tiposManutencoesService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.tiposManutencoesService.loadParams.busy;
    }
}
