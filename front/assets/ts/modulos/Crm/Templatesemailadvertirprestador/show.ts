import angular = require('angular');

export class CrmTemplatesemailadvertirprestadorFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmTemplatesemailadvertirprestador', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        
  
   this.$scope.$on('crm_templatesemailadvertirprestador_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Template de E-mail ao Advertir um Prestador de Serviços foi excluído com sucesso!'
        });
        this.$state.go('crm_templatesemailadvertirprestador', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_templatesemailadvertirprestador_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Template de E-mail ao Advertir um Prestador de Serviços!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    delete(force: boolean) {   

        this.entityService.delete(this.$stateParams.templateemailadvertirprestador, force);
    }


    }
