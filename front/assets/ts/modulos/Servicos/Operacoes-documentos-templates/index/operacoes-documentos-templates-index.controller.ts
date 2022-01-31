import angular = require('angular');
import {OperacoesDocumentosTemplatesService} from "../operacoes-documentos-templates.service";

export class OperacoesDocumentosTemplatesIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'operacoesDocumentosTemplatesService',
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
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public operacoesDocumentosTemplatesService: OperacoesDocumentosTemplatesService,
        public toaster: any,
        public $rootScope: any,
        public $location: any,
        public nsjRouting: any
    ) {
        operacoesDocumentosTemplatesService.filter = $stateParams.q ? $stateParams.q : '';
        operacoesDocumentosTemplatesService.filters = {};
        operacoesDocumentosTemplatesService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                operacoesDocumentosTemplatesService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'operacaodocumentotemplate') {
                operacoesDocumentosTemplatesService.constructors[i] = $stateParams[i];
            }
        }
        this.service = operacoesDocumentosTemplatesService;
        this.filters = operacoesDocumentosTemplatesService.filters;
        this.entities = operacoesDocumentosTemplatesService.reload();
        this.fields = $rootScope.FIELDS_Operacoesdocumentostemplates;
        $scope.$on('operacoesdocumentostemplates_deleted', (event: any) => {
            this.operacoesDocumentosTemplatesService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.operacoesDocumentosTemplatesService.loading_deferred) {
                this.operacoesDocumentosTemplatesService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('operacoesdocumentostemplates_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.operacoesDocumentosTemplatesService.reload();
            }
        });
    }

    search(filter: any) {
        let entities = this.operacoesDocumentosTemplatesService.search(filter);
        let filterURL = angular.copy(this.operacoesDocumentosTemplatesService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, {'q': this.operacoesDocumentosTemplatesService.filter}));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.operacoesDocumentosTemplatesService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.operacoesDocumentosTemplatesService.loadParams.busy;
    }
}
