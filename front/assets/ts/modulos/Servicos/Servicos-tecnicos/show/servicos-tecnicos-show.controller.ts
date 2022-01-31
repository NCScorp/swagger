import angular = require('angular');
import { ServicosTecnicosService } from '../servicos-tecnicos.service';

export class ServicosTecnicosShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'servicosTecnicosService',
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
        public servicosTecnicosService: ServicosTecnicosService,
        public toaster: any,
        public entity: any
    ) {


        this.$scope.$on('servicostecnicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir serviço técnico!'
            });
            this.$state.go('servicostecnicos', angular.extend(this.servicosTecnicosService.constructors));
        });

        this.$scope.$on('servicostecnicos_delete_error', (event: any, args: any) => {
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

        this.constructors = servicosTecnicosService.constructors;
    } delete(force: boolean) {

        this.servicosTecnicosService.delete(this.$stateParams.servicotecnico, force);
    }


}
