import angular = require('angular');
import { TiposOrdensServicosService } from '../tipos-ordens-servicos.service';

export class TiposOrdensServicosShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposOrdensServicosService',
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
        public tiposOrdensServicosService: TiposOrdensServicosService,
        public toaster: any,
        public entity: any
    ) {

        this.$scope.$on('tiposordensservicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir tipo de ordem de serviço!'
            });
            this.$state.go('tiposordensservicos', angular.extend(this.tiposOrdensServicosService.constructors));
        });

        this.$scope.$on('tiposordensservicos_delete_error', (event: any, args: any) => {
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

        this.constructors = tiposOrdensServicosService.constructors;
    }

    delete(force: boolean) {

        this.tiposOrdensServicosService.delete(this.$stateParams.tipoordemservico, force);
    }
}
