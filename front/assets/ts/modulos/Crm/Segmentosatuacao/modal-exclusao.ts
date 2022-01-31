import angular = require('angular');

export class ModalExclusaoSegmentoAtuacaoController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    
    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any
    ) {
    }

    valid (){
        return true;
    }

    submit() {
        this.submitted = true;
        if (this.valid()) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Erro!'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

}

export class ModalExclusaoSegmentoAtuacaoService {
    static $inject = ['$uibModal', 'nsjRouting', '$http'];

    constructor(public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    open(parameters: any) {
        return this.$uibModal.open({
            template: require('./modal-exclusao.html'),
            controller: 'ModalExclusaoSegmentoAtuacaoController',
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
