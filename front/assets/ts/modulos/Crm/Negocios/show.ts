import angular = require('angular');

export class CrmNegociosFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmNegocios', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'documento') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
        
  
   this.$scope.$on('crm_negocios_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Negócio foi Excluído com sucesso!'
        });
        this.$state.go('crm_negocios', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_negocios_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Negócio!'
            });
        }
    });

    this.constructors = entityService.constructors;
    }    delete() {
    let modal = this.CrmNegociosFormDefaultService.open({ 'documento': entity.documento });
    modal.result.then(() => {
        this.entityService
                .get(this.$stateParams.documento)
                .then((data: any) => {
                    this.$state.go('crm_negocios', angular.extend(this.entityService.constructors));
                });
    })
    .catch((error: any) => {
                if ( error !== 'backdrop click' && error !== 'fechar' && error != 'escape key press' ) {
            this.toaster.pop({
                    type: 'error',
                    title: error
                });
        }
    });
}

    }
