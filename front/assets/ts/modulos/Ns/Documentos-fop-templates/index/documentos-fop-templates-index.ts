import angular = require('angular');
import {NsDocumentosFopTemplatesService} from "../documentos-fop-templates.service";

export class NsDocumentosFopTemplatesIndexController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'nsDocumentosFopTemplatesService',
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
        public nsDocumentosFopTemplatesService: NsDocumentosFopTemplatesService,
        public toaster: any,
        public $rootScope: any,
        public $location: any,
        public nsjRouting: any
    ) {
        nsDocumentosFopTemplatesService.filter = $stateParams.q ? $stateParams.q : '';
        nsDocumentosFopTemplatesService.filters = {};
        nsDocumentosFopTemplatesService.constructors = {};
        for (let i in $stateParams) {
            if ([].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                nsDocumentosFopTemplatesService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'documentotemplate') {
                nsDocumentosFopTemplatesService.constructors[i] = $stateParams[i];
            }
        }
        this.service = nsDocumentosFopTemplatesService;
        this.filters = nsDocumentosFopTemplatesService.filters;
        this.entities = nsDocumentosFopTemplatesService.reload();
        this.fields = $rootScope.FIELDS_NsDocumentosfoptemplates;
        $scope.$on('ns_documentosfoptemplates_deleted', (event: any) => {
            this.nsDocumentosFopTemplatesService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.nsDocumentosFopTemplatesService.loading_deferred) {
                this.nsDocumentosFopTemplatesService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('ns_documentosfoptemplates_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.nsDocumentosFopTemplatesService.reload();
            }
        });
    }

    search(filter: any) {
        let entities = this.nsDocumentosFopTemplatesService.search(filter);
        let filterURL = angular.copy(this.nsDocumentosFopTemplatesService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, {'q': this.nsDocumentosFopTemplatesService.filter}));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    loadMore() {
        this.nsDocumentosFopTemplatesService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.nsDocumentosFopTemplatesService.loadParams.busy;
    }
}
