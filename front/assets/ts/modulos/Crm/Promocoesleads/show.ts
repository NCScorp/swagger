import angular = require('angular');
import { ModalExclusaoCampOrigemService } from './modal-exclusao';

export class CrmPromocoesleadsFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmPromocoesleads', 'toaster'
    , 'entity', 'modalExclusaoCampOrigemService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoCampOrigemService: ModalExclusaoCampOrigemService ) {
        
  
   this.$scope.$on('crm_promocoesleads_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'A Campanha de Origem foi excluída com sucesso!'
        });
        this.$state.go('crm_promocoesleads', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_promocoesleads_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir a Campanha de Origem!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoCampOrigemService.open(this.$stateParams.promocaolead);
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

        this.entityService.delete(this.$stateParams.promocaolead, force);
    }


    }
