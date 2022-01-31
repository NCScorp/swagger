import angular = require('angular');

export class CrmListadavezvendedoresitensFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmListadavezvendedoresitens', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'listadavezvendedoritem') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
        
  
   this.$scope.$on('crm_listadavezvendedoresitens_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Listadavezvendedoresitens!'
        });
        this.$state.go('crm_listadavezvendedoresitens', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_listadavezvendedoresitens_delete_error', (event: any, args: any) => {
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
    }    delete() {
    let modal = this.CrmListadavezvendedoresitensFormDefaultService.open({ 'listadavezvendedoritem': entity.listadavezvendedoritem });
    modal.result.then(() => {
        this.entityService
                .get(this.$stateParams.listadavezvendedor, this.$stateParams.listadavezvendedoritem)
                .then((data: any) => {
                    this.$state.go('crm_listadavezvendedoresitens', angular.extend(this.entityService.constructors));
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
