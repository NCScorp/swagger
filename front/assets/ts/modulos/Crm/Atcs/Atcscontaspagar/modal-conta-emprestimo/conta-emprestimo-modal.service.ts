import { ui } from "angular";

/**
 * Serviço que vai chamar a modal de solicitar orçamento por e-mail
 */
 export class ContaEmprestimoModalService {
    static $inject = [
        '$uibModal', 
        '$http', 
        'nsjRouting', 
        'toaster',
    ];
    
    constructor(
        public $uibModal: ui.bootstrap.IModalService, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any,
    ) {}
    
    open(parameters: any = {}, subentity: any) {
        return this.$uibModal.open({
            template: require('./conta-emprestimo-modal.html'),
            controller: 'ContaEmprestimoModalController',
            controllerAs: 'crm_atcs_cnt_mprstm_ctrl',
            windowClass: '',
            size: 'md',
            resolve: {
                entity: subentity,
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}