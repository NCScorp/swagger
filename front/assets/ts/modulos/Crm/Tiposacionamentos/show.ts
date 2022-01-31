import angular = require('angular');
import { ModalExclusaoTipoAcionamentoService } from './modal-exclusao';

export class CrmTiposacionamentosFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmTiposacionamentos', 'toaster'
    , 'entity', 'modalExclusaoTipoAcionamentoService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoTipoAcionamentoService: ModalExclusaoTipoAcionamentoService) {
        
  
   this.$scope.$on('crm_tiposacionamentos_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Tipo de Acionamento foi excluído com sucesso!'
        });
        this.$state.go('crm_tiposacionamentos', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_tiposacionamentos_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Tipo de Acionamento!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoTipoAcionamentoService.open(this.$stateParams.tiposacionamento);
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

        this.entityService.delete(this.$stateParams.tiposacionamento, force);
    }


    }
