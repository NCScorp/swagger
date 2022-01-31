import * as angular from 'angular';
import { TiposServicosShowFormController } from './tipos-servicos-show-form.controller';


export class TiposServicosShowFormComponent implements angular.IComponentOptions {
  static selector = 'tiposServicosShowForm';
  static controller = TiposServicosShowFormController;
  static template = require('./tipos-servicos-show-form.html');
  
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
}