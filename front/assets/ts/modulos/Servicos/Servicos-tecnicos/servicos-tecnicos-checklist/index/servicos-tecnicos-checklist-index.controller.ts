import angular = require('angular');
import { ServicosTecnicosChecklistService } from '../servicos-tecnicos-checklist.service';

export class ServicosTecnicosChecklistIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'servicosTecnicosChecklistService',
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
        public servicosTecnicosChecklistService: ServicosTecnicosChecklistService,
        public toaster: any,
        public $rootScope: any,
        public $location: angular.ILocationService,
        public nsjRouting: any
    ) {
        servicosTecnicosChecklistService.filter = $stateParams.q ? $stateParams.q : '';
        servicosTecnicosChecklistService.filters = {};
        servicosTecnicosChecklistService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                servicosTecnicosChecklistService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'servicotecnicochecklist') {
                servicosTecnicosChecklistService.constructors[i] = $stateParams[i];
            }
        }
        this.service = servicosTecnicosChecklistService;
        this.filters = servicosTecnicosChecklistService.filters;
        this.entities = servicosTecnicosChecklistService.reload();
        this.fields = $rootScope.FIELDS_ServicostecnicosServicostecnicoschecklist;
        $scope.$on('servicostecnicos_servicostecnicoschecklist_deleted', (event: any) => {
            this.servicosTecnicosChecklistService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.servicosTecnicosChecklistService.loading_deferred) {
                this.servicosTecnicosChecklistService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('servicostecnicos_servicostecnicoschecklist_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.servicosTecnicosChecklistService.reload();
            }
        });
    }

    search(filter: any) {
        let entities = this.servicosTecnicosChecklistService.search(filter);
        let filterURL = angular.copy(this.servicosTecnicosChecklistService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.servicosTecnicosChecklistService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.servicosTecnicosChecklistService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.servicosTecnicosChecklistService.loadParams.busy;
    }
}
