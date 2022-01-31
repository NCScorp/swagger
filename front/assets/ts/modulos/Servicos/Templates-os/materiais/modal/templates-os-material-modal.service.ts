
import angular = require('angular');

export class TemplatesOrdemServicoMaterialModalService {
    static $inject = [
        '$uibModal'
    ];

    constructor(
        public $uibModal: angular.ui.bootstrap.IModalService
    ) {}

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./templates-os-material-modal.html'),
            controller: 'templatesOrdemServicoMaterialModalController',
            controllerAs: 'tpt_os_mat_mdl_cntrllr',
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
