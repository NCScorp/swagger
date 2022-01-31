import * as angular from 'angular';

import { TemplatesOrdemServicoShowFormController } from './templates-os-show-form.controller';


export class templatesOsShowFormComponent implements angular.IComponentOptions {
  static selector = 'templatesOsShowForm';
  static controller = TemplatesOrdemServicoShowFormController;
  static template = require('./templates-os-show-form.html');
  
  static bindings = {
    entity: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
}