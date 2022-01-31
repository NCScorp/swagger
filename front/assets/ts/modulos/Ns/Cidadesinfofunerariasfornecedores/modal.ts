import angular = require('angular');

export class NsCidadesinfofunerariasfornecedoresFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.cidadeinfofunerariafornecedor ? 'update' : 'insert';
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

export class NsCidadesinfofunerariasfornecedoresFormService {
    static $inject = ['NsCidadesinfofunerariasfornecedores', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./modal.html'),
                controller: 'NsCidadesinfofunerariasfornecedoresFormModalController',
                controllerAs: 'ns_cddsnffnrrsfrncdrs_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.identifier) {
                           let entity = this.entityService.get(parameters.cidadeinformacaofuneraria, parameters.identifier);
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
