import angular = require('angular');
import { ModalExclusaoService } from './modal-exclusao';

export class NsPrioridadesFormShowController {
    static $inject = ['utilService', '$scope', '$stateParams', '$state', 'NsPrioridades', 'toaster', 'entity', 'modalExclusaoService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(
        public utilService: any, 
        public $scope: any, 
        public $stateParams: any, 
        public $state: any, 
        public entityService: any, 
        public toaster: any, 
        public entity: any,
        public ModalExclusaoService: ModalExclusaoService
    ) {

            this.$scope.$on('ns_prioridades_deleted', (event: any, args: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'A Prioridade foi excluída com sucesso!'
                });
                this.$state.go('ns_prioridades', angular.extend(this.entityService.constructors));
            });
    
            this.$scope.$on('ns_prioridades_delete_error', (event: any, args: any) => {
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
                            title: 'Ocorreu um erro ao excluir a Prioridade!'
                        });
                }
            });

        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'prioridade') {
                entityService.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
            }
        }
        this.constructors = entityService.constructors;
    }

    abrirModalExclusao(){
        let modal = this.ModalExclusaoService.open(this.$stateParams.prioridade);
        modal.result.then((response: any) => {
            this.delete(true); 
        })
        .catch((error: any) => {
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }

    delete(force: boolean) {

        this.entityService.delete(this.$stateParams.prioridade, force);
    }

}
