import angular = require('angular');
import { ModalExclusaoHistoricoPadraoService } from './modal-exclusao';

export class CrmHistoricospadraoFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmHistoricospadrao', 'toaster'
    , 'entity', 'modalExclusaoHistoricoPadraoService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoHistoricoPadraoService: ModalExclusaoHistoricoPadraoService) {
        
  
   this.$scope.$on('crm_historicospadrao_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Histórico Padrão foi excluído com sucesso!'
        });
        this.$state.go('crm_historicospadrao', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_historicospadrao_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Histórico Padrão!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoHistoricoPadraoService.open(this.$stateParams.historicopadrao);
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

        this.entityService.delete(this.$stateParams.historicopadrao, force);
    }


    }
