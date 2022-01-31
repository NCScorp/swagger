import angular = require('angular');

import { OperacoesOrdensServicosService } from '../operacoes-ordens-servicos.service';
import {TransitionService} from '@uirouter/core';

export class OperacoesOrdensServicosIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'operacoesOrdensServicosService',
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
        public operacoesOrdensServicosService: OperacoesOrdensServicosService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService
    ) {
        operacoesOrdensServicosService.filter = $stateParams.q ? $stateParams.q : '';
        operacoesOrdensServicosService.filters = {};
        operacoesOrdensServicosService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                operacoesOrdensServicosService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'operacaoordemservico') {
                operacoesOrdensServicosService.constructors[i] = $stateParams[i];
            }
        }
        this.service = operacoesOrdensServicosService;
        this.filters = operacoesOrdensServicosService.filters;
        this.fields = $rootScope.FIELDS_Operacoesordensservicos;

        $scope.$on('operacoesordensservicos_list_finished', (event, entities)=>{
            this.entities = entities;
        })

        $scope.$on('operacoesordensservicos_deleted', (event: any) => {
            this.operacoesOrdensServicosService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.operacoesOrdensServicosService.loading_deferred) {
                this.operacoesOrdensServicosService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('operacoesordensservicos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.operacoesOrdensServicosService.reload();
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
    
    $onInit(){
        this.operacoesOrdensServicosService.reload();
    }

    search(filter: any) {
        let entities = this.operacoesOrdensServicosService.search(filter);
        let filterURL = angular.copy(this.operacoesOrdensServicosService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.operacoesOrdensServicosService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.operacoesOrdensServicosService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.operacoesOrdensServicosService.loadParams.busy;
    }
}
