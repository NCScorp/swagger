import angular = require('angular');
import { ModalExclusaoAcordoTerceirizacaoServService } from './modal-exclusao';

export class CrmAcordosterceirizacoesservicosFormShowController {
    static $inject = [
        'utilService', 
        '$scope', 
        '$stateParams', 
        '$state', 
        'CrmAcordosterceirizacoesservicos', 
        'toaster', 
        'entity',
        'ModalExclusaoAcordoTerceirizacaoServService'
    ];
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
        public ModalExclusaoAcordoTerceirizacaoServService: ModalExclusaoAcordoTerceirizacaoServService
    ){

        this.$scope.$on('crm_acordosterceirizacoesservicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Acordo de Terceirização de Serviços foi excluído com sucesso!'
            });
            this.$state.go('crm_acordosterceirizacoesservicos', angular.extend(this.entityService.constructors));
        });

        this.$scope.$on('crm_acordosterceirizacoesservicos_delete_error', (event: any, args: any) => {
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
                    title: 'Ocorreu um erro ao excluir o Acordo de Terceirização de Serviços!'
                });
            }
        });

        this.constructors = entityService.constructors;

    } 

    abrirModalExclusao(){
        let modal = this.ModalExclusaoAcordoTerceirizacaoServService.open(this.$stateParams.acordoterceirizacaoservico);
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

        this.entityService.delete(this.$stateParams.acordoterceirizacaoservico, force);
    }

}
