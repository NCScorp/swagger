import angular = require('angular');

export class NsFornecedoresFormShowController {
    static $inject = [
        'NsFornecedoresFormSuspenderService',
        'NsFornecedoresFormFornecedorreativarService',
        'NsFornecedoresFormFornecedoradvertirService',
        'utilService', '$scope', '$stateParams',
        '$state', 'NsFornecedores', 'toaster',
        'entity',
        'FornecedoresImagemModalService'
    ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(
        public NsFornecedoresFormSuspenderService: any,
        public NsFornecedoresFormFornecedorreativarService: any,
        public NsFornecedoresFormFornecedoradvertirService: any,
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public entityService: any,
        public toaster: any,
        public entity: any,
        public FornecedoresImagemModalService: any
    ) {
        this.constructors = entityService.constructors;
    }
    
    abrirModalUploadLogo() {
        let modal = this.FornecedoresImagemModalService.open({}, this.entity);
        modal.result.then((retorno: any) => {
            if (retorno.tipo == "sucesso") {
                this.entity.pathlogo = retorno.urlImagem
            }
        })
            .catch((error: any) => { });
    }

    suspender(entity: any) {
        let modal = this.NsFornecedoresFormSuspenderService.open({ 'fornecedor': entity.fornecedor });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }
    fornecedorreativar(entity: any) {
        let modal = this.NsFornecedoresFormFornecedorreativarService.open({ 'fornecedor': entity.fornecedor });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }
    fornecedoradvertir(entity: any) {
        let modal = this.NsFornecedoresFormFornecedoradvertirService.open({ 'fornecedor': entity.fornecedor });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }
}
