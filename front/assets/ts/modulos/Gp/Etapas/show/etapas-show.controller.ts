import angular from "angular";
import { EtapasService } from "../etapas.service";

export class EtapasShowController implements angular.IController {
  static $inject = [
    'utilService',
    '$scope',
    '$stateParams',
    '$state',
    'etapasService',
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
    public etapasService: EtapasService,
    public toaster: any,
    public entity: any
  ) {


    this.$scope.$on('etapas_deleted', (event: any, args: any) => {
      this.toaster.pop({
        type: 'success',
        title: 'Sucesso ao excluir etapa!'
      });
      this.$state.go('etapas', angular.extend(this.etapasService.constructors));
    });

    this.$scope.$on('etapas_delete_error', (event: any, args: any) => {
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

    this.constructors = etapasService.constructors;
  } delete(force: boolean) {
    this.etapasService.delete(this.$stateParams.projetoetapa, force);
  }
}
