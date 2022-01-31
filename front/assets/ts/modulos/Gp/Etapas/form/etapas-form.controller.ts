import angular = require('angular');
import { IEtapas } from '../models/etapas.model';

export class EtapasFormController implements angular.IController {
  static $inject = ['$scope'];

  public entity: IEtapas;
  public form: angular.IFormController;

  constructor(public $scope: angular.IScope) {
  }

  $onInit() {
    this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
      if (newValue !== oldValue) {
        this.form.$setDirty();
      }
    }, true);
  }
}
