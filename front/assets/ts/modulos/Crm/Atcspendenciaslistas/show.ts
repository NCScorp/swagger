import angular = require('angular');

export class CrmAtcspendenciaslistasFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcspendenciaslistas', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negociopendencialista') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
        
  
   this.$scope.$on('crm_atcspendenciaslistas_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Atcspendenciaslistas!'
        });
        this.$state.go('crm_atcspendenciaslistas', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_atcspendenciaslistas_delete_error', (event: any, args: any) => {
        if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
            this.toaster.pop(
            {
                type: 'error',
                title: args.response.data.message
            });
        } else {
            this.toaster.pop(
            {
                type: 'error',
                title: 'Ocorreu um erro ao tentar excluir.'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    delete(force: boolean) {   

        this.entityService.delete(this.$stateParams.negociopendencialista, force);
    }


    }
