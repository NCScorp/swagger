import angular = require('angular');

export class CrmAtcsareaspendenciaslistasFormModalController {
    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.negocioareapendencialista ? 'update' : 'insert';
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

export class CrmAtcsareaspendenciaslistasFormService {
    static $inject = ['CrmAtcsareaspendenciaslistas', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./../../Crm/Atcsareaspendenciaslistas/modal.html'),
            controller: 'CrmAtcsareaspendenciaslistasFormModalController',
            controllerAs: 'crm_tcsrspndncslsts_frm_dflt_cntrllr',
            windowClass: '',
            resolve: {
                entity: () => {
                    if (parameters.identifier && !subentity) {
                       let entity = this.entityService.get(parameters.atcarea, parameters.identifier);
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
