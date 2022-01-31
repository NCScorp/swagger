import angular = require('angular');

export class CrmAtcsconfiguracoestemplatesemailsFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcsconfiguracoestemplatesemails', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        
  
   this.$scope.$on('crm_atcsconfiguracoestemplatesemails_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'A Configuração Template de Emails foi excluída com sucesso!'
        });
        this.$state.go('crm_atcsconfiguracoestemplatesemails', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_atcsconfiguracoestemplatesemails_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir a Configuração Template de Emails!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    delete(force: boolean) {   

        this.entityService.delete(this.$stateParams.atcconfiguracaotemplateemail, force);
    }


    }
