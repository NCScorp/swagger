import angular = require('angular');

export class ModalVisualizarMaisContatosController {
    static $inject = ['$uibModalInstance', 'entity', 'constructors'];
    
    constructor(
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any
    ) {
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

}

export class ModalVisualizarMaisContatosService {
    static $inject = ['$uibModal', 'nsjRouting'];

    constructor(public $uibModal: any, public nsjRouting: any) {
    }
    
    open(parameters: any) {
        return this.$uibModal.open({
            template: require('./modal-visualizar-mais-contatos.html'),
            controller: 'ModalVisualizarMaisContatosController',
            controllerAs: 'mdl_vslzr_ms_cntts_cntrllr',
            windowClass: 'modal-md-wrapper',
            size: 'mais-contatos',
            backdrop: 'static',
            resolve: {
                entity: async () => {
                    let contatos = parameters;
                    return contatos;
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}
