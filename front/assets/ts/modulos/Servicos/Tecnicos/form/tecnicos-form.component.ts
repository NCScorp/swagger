import * as angular from 'angular';
import { TecnicosFormController } from './tecnicos-form.controller';


export class TecnicosFormComponent implements angular.IComponentOptions {
  static selector = 'tecnicosForm';
  static controller = TecnicosFormController;
  static template = require('./tecnicos-form.html');
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