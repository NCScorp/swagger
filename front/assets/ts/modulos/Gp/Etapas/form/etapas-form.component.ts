import * as angular from 'angular';
import { EtapasFormController } from './etapas-form.controller';

export class EtapasFormComponent implements angular.IComponentOptions {
  static selector = 'etapasForm';
  static controller = EtapasFormController;
  static template = require('./etapas-form.html');
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