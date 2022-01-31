import angular = require('angular');
import { CrmOrcamentos } from '../../Orcamentos/factory';

export class ModalExclusaoItemOrcamentoController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors', '$rootScope', 'CrmOrcamentos'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    
    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        public $rootScope: any,
        public CrmOrcamentos: CrmOrcamentos
    ) {
    }

    valid (){
        return true;
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    confirmaExclusao(){
        this.$uibModalInstance.close();
    }



}

export class ModalExclusaoItemOrcamentoService {
    static $inject = ['$uibModal', 'nsjRouting', '$http'];

    constructor(public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    open(parameters: any) {
        return this.$uibModal.open({
            template: require('./modal-exclusao.html'),
            controller: 'ModalExclusaoItemOrcamentoController',
            controllerAs: 'mdl_xcls_cntrllr',
            windowClass: 'modal-md-wrapper',
            backdrop: 'static',
            resolve: {
                entity: async () => {
                    let entity = {'parameters': parameters};
                    return entity;
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}
