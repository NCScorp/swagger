import angular = require('angular');

export class CrmContratoPaganteAcoesModalController {
    static $inject = ['toaster', '$uibModalInstance', 'entity'];

    public action: string;
    public form: any;
    public submitted: boolean = false;
    public tipoDivisao: any = this.entity.modoDivisao[0];
    public selecionados: Map<any, any> = new Map();
    public selecionado: any = null;

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
    ) {

    }

    resetaSelecionados() {
        this.selecionados.clear();
    }

    manipularResponsabilidade(responsavel, valor) {
        // Quando for sem divisão sempre limpo os selecionados 
        // para garantir que apenas um vai ser selecionado
        if (this.tipoDivisao.valor === 'semDivisao') {
            this.resetaSelecionados();
        }

        if (this.selecionados.has(responsavel.cliente)) {
            this.selecionados.delete(responsavel.cliente);
        } else {
            this.selecionados.set(responsavel.cliente, responsavel);
        }
    }
    mapToArray(map) {
        return Array.from(map.values(), (x: any) => x.cliente);
    }
    valid() {
        return this.selecionados.size > 0;
    }

    submit() {
        this.submitted = true;
        const entity = {
            tipodivisao: this.tipoDivisao,
            selecionados: this.mapToArray(this.selecionados),
        }

        if (this.valid()) {
            this.$uibModalInstance.close(entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Nenhum responsável selecionado.'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmContratoPaganteAcoesModalService {
    static $inject = ['CrmPropostascapitulos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }

    open(parameters: any) {
        return this.$uibModal.open({
            template: require('../../Crm/Atcs/contrato-mais-pagante-acoes-modal.html'),
            controller: 'CrmContratoPaganteAcoesModalController',
            controllerAs: 'crm_cntrt_pgnt_cs_mdl_cntrllr',
            windowClass: 'modal-md-wrapper',
            resolve: {
                entity: async () => {
                    let entity = angular.copy(parameters);
                    return entity;
                },
            }
        });
    }
}
