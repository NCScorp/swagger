import angular = require('angular');

export class ModalExclusaoDocumentoController {
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
            this.$uibModalInstance.close('excluir');
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Erro!'
            });
        }
    }

    close() {
        //Não manterá o Tipo de Documento
        this.$uibModalInstance.close('manter');
    }

}

export class ModalExclusaoDocumentoService {
    static $inject = ['$uibModal', 'nsjRouting', '$http'];

    constructor(public $uibModal: any, public nsjRouting: any, public $http: any) {
    }
    
    open(parameters: any) {

        return this.$uibModal.open({
            template: require('./modal-exclusao-documento.html'),
            controller: 'ModalExclusaoDocumentoController',
            controllerAs: 'mdl_xcls_dcmnt_cntrllr',
            windowClass: 'modal-md-wrapper',
            backdrop: 'static',
            resolve: {
                entity: async () => {
                    //let entity = {'parameters': parameters};
                    let entity = parameters.atctipodocumentorequisitante.dados.tipodocumento;
                    return entity;
                },
                constructors: () => {
                    return parameters;
                }
            }
        });

    }
}
