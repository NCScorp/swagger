import angular = require('angular');
import { TecnicosService } from '../tecnicos.service';

export class TecnicosShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tecnicosService',
        'toaster',
        'entity',
    ];

    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;
    private logopadrao = 'https://s3-sa-east-1.amazonaws.com/imagens.nasajon/geral/avatar-3x4.svg';

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public tecnicosService: TecnicosService,
        public toaster: any,
        public entity: any
    ) {
        this.entity.pathlogo = this.entity.pathlogo ? this.entity.pathlogo : this.logopadrao;
        
        this.$scope.$on('tecnicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir técnico!'
            });
            this.$state.go('tecnicos', angular.extend(this.tecnicosService.constructors));
        });

        this.$scope.$on('tecnicos_delete_error', (event: any, args: any) => {
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

        this.constructors = tecnicosService.constructors;
    } delete(force: boolean) {

        this.tecnicosService.delete(this.$stateParams.tecnico, force);
    }


}
