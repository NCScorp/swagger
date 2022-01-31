import angular = require('angular');
import { TransitionService } from '@uirouter/core'
import { NsSeriesService } from '../series.service';

export class NsSeriesIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'nsSeriesService',
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
        public nsSeriesService: NsSeriesService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any,
        public $transitions: TransitionService
    ) {
        nsSeriesService.filter = $stateParams.q ? $stateParams.q : '';
        nsSeriesService.filters = {};
        nsSeriesService.constructors = {};
        for (let i in $stateParams) {
            if (['situacao', 'estabelecimento',].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                nsSeriesService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'serie') {
                nsSeriesService.constructors[i] = $stateParams[i];
            }
        }
        this.service = nsSeriesService;
        this.filters = nsSeriesService.filters;
        this.entities = nsSeriesService.reload();
        this.fields = $rootScope.FIELDS_NsSeries;
        $scope.$on('ns_series_deleted', (event: any) => {
            this.nsSeriesService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.nsSeriesService.loading_deferred) {
                this.nsSeriesService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('ns_series_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.nsSeriesService.reload();
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
        let entities = this.nsSeriesService.search(filter);
        let filterURL = angular.copy(this.nsSeriesService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.nsSeriesService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.nsSeriesService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.nsSeriesService.loadParams.busy;
    }
}
