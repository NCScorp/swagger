import angular = require('angular');
import { TiposServicosService } from '../tipos-servicos.service';

export class TiposServicosShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposServicosService',
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
        public tiposServicosService: TiposServicosService,
        public toaster: any,
        public entity: any
    ) {


        this.$scope.$on('tiposservicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir tipo de serviço!'
            });
            this.$state.go('tiposservicos', angular.extend(this.tiposServicosService.constructors));
        });

        this.$scope.$on('tiposservicos_delete_error', (event: any, args: any) => {
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

        this.constructors = tiposServicosService.constructors;
    } delete(force: boolean) {

        this.tiposServicosService.delete(this.$stateParams.tiposervico, force);
    }
}
