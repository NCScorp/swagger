import * as angular from 'angular';
import { TemplatesOrdemServicoFormController } from './templates-os-form.controller';

export class TemplatesOrdemServicoFormComponent implements angular.IComponentOptions {
  static selector = 'templatesOsForm';
  static controller = TemplatesOrdemServicoFormController;
  static template = require('./templates-os-form.html');
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
