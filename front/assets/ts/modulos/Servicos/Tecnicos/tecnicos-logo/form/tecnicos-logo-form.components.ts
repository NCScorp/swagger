import angular = require('angular');
import { TecnicosLogoFormController } from './tecnicos-logo-form.controller';

export class TecnicosLogoFormComponent implements angular.IComponentOptions {
  static selector = 'tecnicosLogoForm';
  static controller = TecnicosLogoFormController;
  static template = require('./tecnicos-logo-form.html');
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