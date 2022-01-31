import angular = require('angular');

export class CrmAtcsDadosSeguradorasFormModalController {
    static $inject = ['toaster', '$uibModalInstance','entity'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    constructor(public toaster: any, public $uibModalInstance: any,public entity:any) {
    }

    confirm() {
        this.$uibModalInstance.close(true);
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmAtcsDadosSeguradorasFormService {
    static $inject = ['$uibModal'];

    constructor(public $uibModal: any) {
    }

    /* Incluindo o pai como parametro opcional*/
    open(params) {
        return this.$uibModal.open({
            template: require('./../../Crm/Atcsdadosseguradoras/modal.html'),
            controller: 'CrmAtcsDadosSeguradorasFormModalController',
            controllerAs: 'crm_tcsddssgrdrs_frm_dflt_cntrllr',
            windowClass: '',
            resolve: {
                entity : () => ({ message: params.message , title:params.title})
            }
        });
    }

}
