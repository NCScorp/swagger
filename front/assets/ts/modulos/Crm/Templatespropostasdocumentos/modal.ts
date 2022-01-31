import angular = require('angular');

export class CrmTemplatespropostasdocumentosFormModalController {
    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors','collection'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any,public collection:any) {
        // this.action = entity.templatepropostadocumento ? 'update' : 'insert';
        this.action = entity.tipodocumento ? 'update' : 'insert';
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

export class CrmTemplatespropostasdocumentosFormService {
    static $inject = ['CrmTemplatespropostasdocumentos', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any, collection?: any) {
        return this.$uibModal.open({
            // template: require('./modal.html'),
            template: require('../../Crm/Templatespropostasdocumentos/modal.html'),
            controller: 'CrmTemplatespropostasdocumentosFormModalController',
            controllerAs: 'crm_tmpltsprpstsdcmnts_frm_dflt_cntrllr',
            windowClass: '',
            size:'lg',
            resolve: {

                entity: () => {
                    if (parameters.identifier) {
                        let entity = this.entityService.get(parameters.templateproposta, parameters.identifier);
                        return entity;
                    } else {
                        return angular.copy(subentity);
                    }
                },
                constructors: () => {
                    return parameters;
                },
                collection: () => {
                    return collection;
                }
            }
        });
    }

}
