import angular = require('angular');

import { NsSeriesService } from '../series.service';

export class NsSeriesShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'nsSeriesService',
        'toaster',
        'entity',
    ];

    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public nsSeriesService: NsSeriesService,
        public toaster: any,
        public entity: any
    ) {


        this.$scope.$on('ns_series_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir Série!'
            });
            this.$state.go('ns_series', angular.extend(this.nsSeriesService.constructors));
        });

        this.$scope.$on('ns_series_delete_error', (event: any, args: any) => {
            if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: args.response.data.message
                    });
            } else {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: 'Ocorreu um erro ao tentar excluir.'
                    });
            }
        });

        this.constructors = nsSeriesService.constructors;
    } 
    
    delete(force: boolean) {

        this.nsSeriesService.delete(this.$stateParams.serie, force);
    }
}
