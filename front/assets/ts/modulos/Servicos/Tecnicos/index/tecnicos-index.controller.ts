import angular = require('angular');
import { TransitionService } from '@uirouter/core';
import { TecnicosService } from '../tecnicos.service';

export class TecnicosIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tecnicosService',
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
        public tecnicosService: TecnicosService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService,
    ) {
        tecnicosService.filter = $stateParams.q ? $stateParams.q : '';
        tecnicosService.filters = {};
        tecnicosService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                tecnicosService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'tecnico') {
                tecnicosService.constructors[i] = $stateParams[i];
            }
        }
        this.service = tecnicosService;
        this.filters = tecnicosService.filters;
        this.entities = tecnicosService.reload();
        this.fields = $rootScope.FIELDS_Tecnicos;
        $scope.$on('tecnicos_deleted', (event: any) => {
            this.tecnicosService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.tecnicosService.loading_deferred) {
                this.tecnicosService.loading_deferred.resolve();
            }
        });

        $rootScope.$on('tecnicos_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.tecnicosService.reload();
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
        let entities = this.tecnicosService.search(filter);
        let filterURL = angular.copy(this.tecnicosService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.tecnicosService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.tecnicosService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.tecnicosService.loadParams.busy;
    }
}
