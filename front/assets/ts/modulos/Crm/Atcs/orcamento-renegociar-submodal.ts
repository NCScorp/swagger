import angular = require('angular');

export class CrmOrcamentosRenegociarSubModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    public servicosProdutos: string;
    
    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any
    ) {
    }

    valid (){
        if(this.entity.motivo === '') return false;
        if(this.entity.motivo === null) return false;
        if(this.entity.motivo === undefined) return false;
        return true;
    }

    submit() {
        this.submitted = true;
        if (this.valid()) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'O motivo não deve estar vazio.'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmOrcamentosRenegociarSubModalService {
    static $inject = ['CrmPropostascapitulos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    open(parameters: any, subentity: any, paiId: any) {
            return this.$uibModal.open({
                template: require('../../Crm/Atcs/orcamento-renegociar-modal.html'),
                controller: 'CrmOrcamentosRenegociarSubModalController',
                controllerAs: 'crm_orcmnts_rngcr_sb_mdl_cntrllr',
                windowClass: 'modal-md-wrapper',
                resolve: {
                entity: async () => {
                    let entity = {'motivo': '', 'nome': parameters.row.branch.servicosProdutos};
                    return entity;
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}
