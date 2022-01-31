import angular = require('angular');
import { Object } from 'core-js';

export class CrmConfiguracoestaxasadministrativasListController {
    static $inject = ['utilService', '$scope', '$stateParams', '$state', 'CrmConfiguracoestaxasadministrativas', 'toaster'
        ,
        '$rootScope', '$location', 'nsjRouting'];
    public busy: boolean = false;
    public entities: any;
    public fields: any;
    public service: any;
    public filters: any;
    public selected: any = [];
    public selectPage: boolean = false;
    public selectAll: boolean = false;
    constructor(public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
        ,
        public $rootScope: any, public $location: any, public nsjRouting: any) {
        entityService.filter = $stateParams.q ? $stateParams.q : '';
        entityService.filters = {};
        entityService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                entityService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'configuracaotaxaadm') {
                entityService.constructors[i] = $stateParams[i];
            }
        }
        this.service = entityService;
        this.filters = entityService.filters;
        this.entities = entityService.reload();
        this.fields = $rootScope.FIELDS_CrmConfiguracoestaxasadministrativas;
        $scope.$on('crm_configuracoestaxasadministrativas_deleted', (event: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'A Taxa Administrativa foi excluída com sucesso!'
            });
            this.$state.go('crm_configuracoestaxasadministrativas', angular.extend({}, this.entityService.constructors, {}));
            this.entityService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.entityService.loading_deferred) {
                this.entityService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('crm_configuracoestaxasadministrativas_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.entityService.reload();
            }
        });
    }

    search(filter: any) {
        let entities = this.entityService.search(filter);
        let filterURL = angular.copy(this.entityService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.entityService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.entityService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.entityService.loadParams.busy;
    }
}
