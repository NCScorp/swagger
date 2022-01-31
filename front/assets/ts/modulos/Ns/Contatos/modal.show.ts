import angular = require('angular');

export class NsContatosFormModalShowController {
    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
    constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = 'retrieve';
    }
    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class NsContatosFormShowService {
    static $inject = ['NsContatos', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            // template: require('./modal.show.html'),
            template: require('./../../Ns/Contatos/modal.show.html'),
            controller: 'NsContatosFormModalShowController',
            controllerAs: 'ns_cntts_frm_shw_cntrllr',
            windowClass: '',
            backdrop: 'static',
            keyboard: false,
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
