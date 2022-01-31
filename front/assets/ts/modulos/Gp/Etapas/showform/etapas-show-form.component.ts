import * as angular from 'angular';
import { EtapasShowFormController } from './etapas-show-form.controller';


export class EtapasShowFormComponent implements angular.IComponentOptions {
  static selector = 'etapasShowForm';
  static controller = EtapasShowFormController;
  static template = require('./etapas-show-form.html');
  static transclude = true;

  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
    }
}