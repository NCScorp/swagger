import angular = require('angular');

export class CrmComposicoesFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.composicao ? 'update' : 'insert';
    }

    validarCampos(): boolean {
        let valido: boolean = true;

        if (this.entity.quantidade > 2147483648) {
            valido = false;
            this.toaster.pop({
                type: 'error',
                title: 'A quantidade não pode ser maior 2147483648.'
            });
        } 
        
        return valido;
    }
    submit() {
        this.submitted = true;
        
        if (this.form.$valid) {
            this.form.$valid = this.validarCampos();
            if (this.form.$valid) {
                this.$uibModalInstance.close(this.entity);
            }    
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

export class CrmComposicoesFormService {
    static $inject = ['CrmComposicoes', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./../../Crm/Composicoes/modal.html'),
                controller: 'CrmComposicoesFormModalController',
                controllerAs: 'crm_cmpscs_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.composicao) {
                           let entity = this.entityService.get(parameters.composicao);
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
