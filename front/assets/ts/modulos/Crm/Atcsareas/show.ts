import angular = require('angular');
import { ModalExclusaoAtcsAreasService } from './modal-exclusao';

export class CrmAtcsareasFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcsareas', 'toaster'
    , 'entity', 'modalExclusaoAtcsAreasService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoAtcsAreasService: ModalExclusaoAtcsAreasService ) {
        
  
   this.$scope.$on('crm_atcsareas_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'A Área de Atendimento comercial foi excluída com sucesso!'
        });
        this.$state.go('crm_atcsareas', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_atcsareas_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir a Área de Atendimento comercial!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoAtcsAreasService.open(this.$stateParams.negocioarea);
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

        this.entityService.delete(this.$stateParams.negocioarea, force);
    }


    }
