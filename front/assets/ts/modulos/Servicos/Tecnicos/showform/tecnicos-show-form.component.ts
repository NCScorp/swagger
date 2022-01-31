import * as angular from 'angular';
import { TecnicosShowFormController } from './tecnicos-show-form.controller';


export class TecnicosShowFormComponent implements angular.IComponentOptions {
  static selector = 'tecnicosShowForm';
  static controller = TecnicosShowFormController;
  static template = require('./tecnicos-show-form.html');
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