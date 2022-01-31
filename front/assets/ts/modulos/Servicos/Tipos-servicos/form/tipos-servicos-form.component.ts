import * as angular from 'angular';
import { TiposServicosFormController } from './tipos-servicos-form.controller';


export class TiposServicosFormComponent implements angular.IComponentOptions {
  static selector = 'tiposServicosForm';
  static controller = TiposServicosFormController;
  static template = require('./tipos-servicos-form.html');
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
}
