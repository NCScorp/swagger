import angular = require('angular');

export class NsContatosFormModalController {
    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.contato ? 'update' : 'insert';
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

export class NsContatosFormService {
    static $inject = ['NsContatos', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            // template: require('./modal.html'),
            template: require('./../../Ns/Contatos/modal.html'),
            controller: 'NsContatosFormModalController',
            controllerAs: 'ns_cntts_frm_dflt_cntrllr',
            backdrop: 'static',
            keyboard: false,
            windowClass: '',
            resolve: {
                entity: () => {
                    // if (parameters.identifier) {
                    if (parameters.identifier && !subentity) {
                        let entity = this.entityService.get(parameters.pessoa, parameters.identifier);
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
