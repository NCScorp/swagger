import angular = require('angular');

export class CrmMalotesdocumentosFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmMalotesdocumentos', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        
  
   this.$scope.$on('crm_malotesdocumentos_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Malotesdocumentos!'
        });
        this.$state.go('crm_malotesdocumentos', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_malotesdocumentos_delete_error', (event: any, args: any) => {
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

        this.entityService.delete(this.$stateParams.malotedocumento, force);
    }


    }
