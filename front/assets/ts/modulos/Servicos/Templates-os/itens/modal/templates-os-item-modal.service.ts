
import angular = require('angular');

export class TemplatesOrdemServicoItemModalService {
    static $inject = [
        '$uibModal'
    ];

    constructor(
        public $uibModal: angular.ui.bootstrap.IModalService
    ) {}

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./templates-os-item-modal.html'),
            controller: 'templatesOrdemServicoItemModalController',
            controllerAs: 'tpt_os_item_mdl_cntrllr',
            windowClass: '',
            resolve: {
                entity: () => {
                    return angular.copy(subentity);
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}
