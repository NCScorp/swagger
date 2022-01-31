import * as angular from 'angular';

import { TemplatesOrdemServicoItemFormController } from './templates-os-item-form.controller';

export class TemplatesOrdemServicoItemFormComponent implements angular.IComponentOptions {
  static selector = 'templatesOsItemForm';
  static controller = TemplatesOrdemServicoItemFormController;
  static template = require('./templates-os-item-form.html');
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