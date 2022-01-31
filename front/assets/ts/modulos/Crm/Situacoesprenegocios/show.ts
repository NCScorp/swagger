import angular = require('angular');
import { ModalExclusaoSituacaoNegocioService } from './modal-exclusao';

export class CrmSituacoesprenegociosFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmSituacoesprenegocios', 'toaster'
    , 'entity', 'modalExclusaoSituacaoNegocioService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoSituacaoNegocioService: ModalExclusaoSituacaoNegocioService ) {
        
  
   this.$scope.$on('crm_situacoesprenegocios_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'A Situação do Negócio foi excluída com sucesso!'
        });
        this.$state.go('crm_situacoesprenegocios', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_situacoesprenegocios_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir a Situação do Negócio!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoSituacaoNegocioService.open(this.$stateParams.situacaoprenegocio);
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

        this.entityService.delete(this.$stateParams.situacaoprenegocio, force);
    }


    }
