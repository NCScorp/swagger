import angular = require('angular');

export class CrmPropostascapitulosFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.propostacapitulo ? 'update' : 'insert';
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

export class CrmPropostascapitulosFormService {
    static $inject = ['CrmPropostascapitulos', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    /* Incluindo o pai como parametro opcional*/
    open(parameters: any, subentity: any, paiId: any) {
            return this.$uibModal.open({
                template: require('./../../Crm/Propostascapitulos/modal.html'),
                controller: 'CrmPropostascapitulosFormModalController',
                controllerAs: 'crm_prpstscptls_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.propostacapitulo) {
                           let entity = this.entityService.get(parameters.proposta, parameters.propostacapitulo);
                           entity.pai = paiId;
                           return entity;
                        } else {
                           subentity.pai = paiId;
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
