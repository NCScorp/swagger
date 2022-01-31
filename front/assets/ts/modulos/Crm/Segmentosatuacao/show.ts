import angular = require('angular');
import { ModalExclusaoSegmentoAtuacaoService } from './modal-exclusao';

export class CrmSegmentosatuacaoFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmSegmentosatuacao', 'toaster'
    , 'entity', 'modalExclusaoSegmentoAtuacaoService'];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any, public ModalExclusaoSegmentoAtuacaoService: ModalExclusaoSegmentoAtuacaoService ) {
        
  
   this.$scope.$on('crm_segmentosatuacao_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Segmento de Atuação foi excluído com sucesso!'
        });
        this.$state.go('crm_segmentosatuacao', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_segmentosatuacao_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Segmento de Atuação!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    
    
    abrirModalExclusao(){
        let modal = this.ModalExclusaoSegmentoAtuacaoService.open(this.$stateParams.segmentoatuacao);
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

        this.entityService.delete(this.$stateParams.segmentoatuacao, force);
    }


    }
