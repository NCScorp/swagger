import angular = require('angular');
import { ModalExclusaoFuncaoService } from './modal-exclusao';

export class GpFuncoesFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'GpFuncoes', 'toaster'
    , 'entity', 'modalExclusaoFuncaoService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoFuncaoService: ModalExclusaoFuncaoService ) {
        
  
   this.$scope.$on('gp_funcoes_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'A Função foi excluída com sucesso!'
        });
        this.$state.go('gp_funcoes', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('gp_funcoes_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir a Função!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }   
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoFuncaoService.open(this.$stateParams.funcao);
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

        this.entityService.delete(this.$stateParams.funcao, force);
    }


    }
