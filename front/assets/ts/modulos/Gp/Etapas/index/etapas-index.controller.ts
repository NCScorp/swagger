import angular = require('angular');
import { TransitionService } from '@uirouter/core'
import { EtapasService } from '../etapas.service';
import { IEtapas } from '../models/etapas.model';

export class EtapasIndexController implements angular.IController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'etapasService',
        'toaster',
        '$rootScope',
        '$location',
        'nsjRouting',
        '$transitions'
    ];

    public busy: boolean = false;
    public entities: IEtapas[];
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
        public etapasService: EtapasService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService
    ) {

        etapasService.filter = $stateParams.q ? $stateParams.q : '';
        etapasService.filters = {};
        etapasService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                etapasService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'projetoetapa') {
                etapasService.constructors[i] = $stateParams[i];
            }
        }
        this.service = etapasService;
        this.filters = etapasService.filters;
        this.entities = etapasService.reload();
        this.fields = $rootScope.FIELDS_etapas;
        $scope.$on('etapas_deleted', (event: any) => {
            this.etapasService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.etapasService.loading_deferred) {
                this.etapasService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('etapas_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.etapasService.reload();
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
        let entities = this.etapasService.search(filter);
        let filterURL = angular.copy(this.etapasService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.etapasService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.etapasService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.etapasService.loadParams.busy;
    }
}

