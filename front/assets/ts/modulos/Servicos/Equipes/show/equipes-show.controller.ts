import angular = require('angular');
import { EquipesService } from '../equipes.service';

export class EquipesShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'equipesService',
        'toaster',
        'entity',
    ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public equipesService: EquipesService,
        public toaster: any,
        public entity: any
    ) {


        this.$scope.$on('equipes_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir equipe!'
            });
            this.$state.go('equipes', angular.extend(this.equipesService.constructors));
        });

        this.$scope.$on('equipes_delete_error', (event: any, args: any) => {
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

        this.constructors = equipesService.constructors;
    } delete(force: boolean) {

        this.equipesService.delete(this.$stateParams.equipetecnico, force);
    }


}
