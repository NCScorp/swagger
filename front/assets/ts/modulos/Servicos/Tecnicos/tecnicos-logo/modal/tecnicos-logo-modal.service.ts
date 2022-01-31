import angular = require('angular');

export class TecnicosLogoModalService {
    static $inject = ['$uibModal', '$http', 'nsjRouting', 'toaster'];

    constructor(
        public $uibModal: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any
    ) {}


    open(parameters: any, subentity: any) {
        return  this.$uibModal.open({
            template: require('./tecnicos-logo-modal.html'),
            controller: 'tecnicosLogoModalController',
            controllerAs: 'tcnc_upldlgo_cntrllr',
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
