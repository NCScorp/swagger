import angular = require('angular');

export class CrmComposicoesfamiliasFormModalController {
    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.composicaofamilia ? 'update' : 'insert';
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

    submit(){
        if (this.form.$valid) {
            this.form.$valid = this.validarCampos();

            if (this.form.$valid) {
                this.submitted = true;
                if (this.form.$valid) {
                    this.$uibModalInstance.close(this.entity);
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Alguns campos do formulário apresentam erros.'
                    });
                }
                return;
            } 
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
        
    }

    // submit() {
    //     this.submitted = true;
    //     if (this.form.$valid) {
    //         this.$uibModalInstance.close(this.entity);
    //     } else {
    //         this.toaster.pop({
    //             type: 'error',
    //             title: 'Alguns campos do formulário apresentam erros'
    //         });
    //     }
    // }
    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmComposicoesfamiliasFormService {
    static $inject = ['CrmComposicoesfamilias', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./modal.html'),
            controller: 'CrmComposicoesfamiliasFormModalController',
            controllerAs: 'crm_cmpscsfmls_frm_dflt_cntrllr',
            windowClass: '',
            resolve: {
                entity: () => {
                    if (parameters.identifier) {
                        let entity = this.entityService.get(parameters.composicao, parameters.identifier);
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
