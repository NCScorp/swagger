import angular = require('angular');
import { TiposManutencoesService } from '../tipos-manutencoes.service';

export class TiposManutencoesShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposManutencoesService',
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
        public tiposManutencoesService: TiposManutencoesService,
        public toaster: any,
        public entity: any
    ) {


        this.$scope.$on('tiposmanutencoes_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir tipo de manutenção!'
            });
            this.$state.go('tiposmanutencoes', angular.extend(this.tiposManutencoesService.constructors));
        });

        this.$scope.$on('tiposmanutencoes_delete_error', (event: any, args: any) => {
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

        this.constructors = tiposManutencoesService.constructors;
    } delete(force: boolean) {

        this.tiposManutencoesService.delete(this.$stateParams.tipomanutencao, force);
    }


}
