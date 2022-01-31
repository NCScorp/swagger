import angular = require('angular');

export class CrmResponsabilidadesfinanceirasvaloresFormShowController {
    static $inject = [                                                'utilService', '$scope', '$stateParams', '$state', 'CrmResponsabilidadesfinanceirasvalores', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'responsabilidadefinanceiravalor') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
        
  
   this.$scope.$on('crm_responsabilidadesfinanceirasvalores_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Responsabilidadesfinanceirasvalores!'
        });
        this.$state.go('crm_responsabilidadesfinanceirasvalores', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_responsabilidadesfinanceirasvalores_delete_error', (event: any, args: any) => {
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

        this.entityService.delete(this.$stateParams.responsabilidadefinanceiravalor, force);
    }


    }
