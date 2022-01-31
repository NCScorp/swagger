import angular = require('angular');

export class ConfirmacaoFecharPedidoModalController {
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

    getFuncao(){
        return this.entity.parameters.funcao;
    }

    isFecharPedido(){
        if (this.getFuncao() == 'fecharPedido'){
            return true;
        }
        return false;
    }

    isCancelarAtendimento(){
        if (this.getFuncao() == 'cancelarAtendimento'){
            return true;
        }
        return false;
    }

}

export class ConfirmacaoFecharPedidoModalService {
    static $inject = ['$uibModal', 'nsjRouting', '$http'];

    constructor(public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    open(parameters: any, subentity: any, paiId: any) {
            return this.$uibModal.open({
                template: require('./../../Crm/Atcs/confirmacao-fechar-pedido-modal.html'),
                controller: 'ConfirmacaoFecharPedidoModalController',
                controllerAs: 'cnfrmc_fchr_pdd_mdl_cntrllr',
                windowClass: 'modal-md-wrapper',
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
