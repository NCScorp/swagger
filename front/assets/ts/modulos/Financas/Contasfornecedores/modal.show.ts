import angular = require('angular');

export class FinancasContasfornecedoresFormModalShowController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = 'retrieve';
    }
        close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class FinancasContasfornecedoresFormShowService {
    static $inject = ['FinancasContasfornecedores', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./modal.show.html'),
                controller: 'FinancasContasfornecedoresFormModalShowController',
                controllerAs: 'fnncs_cntsfrncdrs_frm_shw_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.identifier) {
                           let entity = this.entityService.get(parameters.fornecedor, parameters.identifier);
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
