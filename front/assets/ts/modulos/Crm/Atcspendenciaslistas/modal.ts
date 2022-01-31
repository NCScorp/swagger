import angular = require('angular');

export class CrmAtcspendenciaslistasFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.negociopendencialista ? 'update' : 'insert';
        // console.debug(this.action);
    }
        submit() {
        this.submitted = true;
        if (this.form.$valid) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
        close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmAtcspendenciaslistasFormService {
    static $inject = ['CrmAtcspendenciaslistas', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./../../Crm/Atcspendenciaslistas/modal.html'),
                controller: 'CrmAtcspendenciaslistasFormModalController',
                controllerAs: 'crm_tcspndncslsts_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.atcpendencialista) {
                           let entity = this.entityService.get(parameters.atc, parameters.atcpendencialista);
                           return entity;
                        } else {
                           return angular.copy(subentity);
                        }
                    },
                    constructors: () => {
                        return parameters;
                    }
                }
            });
    }

}
