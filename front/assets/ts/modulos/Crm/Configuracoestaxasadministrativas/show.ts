import angular = require('angular');
import { ModalExclusaoTaxaAdmService } from './modal-exclusao';

export class CrmConfiguracoestaxasadministrativasFormShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'CrmConfiguracoestaxasadministrativas',
        'toaster',
        'entity',
        'ModalExclusaoTaxaAdmService',
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
        public ModalExclusaoTaxaAdmService: ModalExclusaoTaxaAdmService,
    ) {
        this.constructors = entityService.constructors;
    }

    abrirModalExclusao(){
        let modal = this.ModalExclusaoTaxaAdmService.open(this.$stateParams.configuracaotaxaadm);
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
        this.entityService.delete(this.$stateParams.configuracaotaxaadm, force);
    }

}
