import angular = require('angular');

export class NsTelefonesFormModalShowController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = 'retrieve';
    }
        close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class NsTelefonesFormShowService {
    static $inject = ['NsTelefones', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./modal.show.html'),
                controller: 'NsTelefonesFormModalShowController',
                controllerAs: 'ns_tlfns_frm_shw_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.identifier && !subentity) {
                           let entity = this.entityService.get(parameters.contato, parameters.identifier);
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
