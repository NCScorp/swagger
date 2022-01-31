import angular = require('angular');

export class FornecedoresImagemModalService {
    static $inject = ['$uibModal', '$http', 'nsjRouting', 'toaster'];
 
    constructor(
        public $uibModal: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any
    ) {}
    
 
    open(parameters: any, subentity: any) {
        return  this.$uibModal.open({
            template: require('.././../../Ns/Fornecedores/uploadimagem/modal.html'),
            controller: 'FornecedoresImagemModal',
            controllerAs: 'ns_frncdrs_upldlgo_cntrllr',
            windowClass: '',
            resolve: {
                entity: subentity,
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}

