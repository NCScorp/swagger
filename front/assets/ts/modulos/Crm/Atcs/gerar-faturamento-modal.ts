import angular = require('angular');

export class ModalGerarFaturamentoController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors', '$http', 'nsjRouting', '$rootScope'];

    public atc: string;
    public busy: boolean = false;
    
    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public $rootScope
    ) {
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    // Gerar faturamento
    gerarFaturamento(){

        this.busy = true;

        this.atc = this.entity.atc;
        delete this.entity.atc;

        const response = this.$http({
            method: 'POST',
            data: this.entity,
            url: this.nsjRouting.generate('crm_atcs_gera_contrato_taxa_administrativa', { id: this.atc }),
        }).then((result) => {

            // Disparando broadcast para indicar que o faturamento foi gerado. É usado para mudar modo de visualização da modal da taxa administrativa
            this.$rootScope.$broadcast('crm_atcs_gera_contrato_taxa_administrativa_success');

            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao gerar faturamento!'
            });
            this.busy = false;
            this.close();

        })
        .catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: error.data?.message != null ? error.data.message : error
            });
            this.busy = false;
            this.close();
        });



    }

}

export class ModalGerarFaturamentoService {
    static $inject = ['$uibModal', 'nsjRouting', '$http'];

    constructor(public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    open(parameters: any) {

        return this.$uibModal.open({
            template: require('./gerar-faturamento-modal.html'),
            controller: 'ModalGerarFaturamentoController',
            controllerAs: 'mdl_grrftrmnt_cntrllr',
            windowClass: 'modal-md-wrapper',
            backdrop: 'static',
            resolve: {
                entity: async () => {
                    let entity = parameters;
                    return entity;
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}

