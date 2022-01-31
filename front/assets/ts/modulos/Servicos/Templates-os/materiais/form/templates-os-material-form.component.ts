import * as angular from 'angular';

import { TemplatesOrdemServicoMaterialFormController } from './templates-os-material-form.controller';

export class TemplatesOrdemServicoMaterialFormComponent implements angular.IComponentOptions {
  static selector = 'templatesOsMaterialForm';
  static controller = TemplatesOrdemServicoMaterialFormController;
  static template = require('./templates-os-material-form.html');
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