import * as angular from 'angular';
import { TiposOrdensServicosShowFormController } from './tipos-ordens-servicos-show-form.controller';


export class TiposOrdensServicosShowFormComponent implements angular.IComponentOptions {
  static selector = 'tiposOrdensServicosShowForm';
  static controller = TiposOrdensServicosShowFormController;
  static template = require('./tipos-ordens-servicos-show-form.html');
  
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
    }
}