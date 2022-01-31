import * as angular from 'angular';
import { TiposOrdensServicosFormController } from './tipos-ordens-servicos-form.controller';


export class TiposOrdensServicosFormComponent implements angular.IComponentOptions {
  static selector = 'tiposOrdensServicosForm';
  static controller = TiposOrdensServicosFormController;
  static template = require('./tipos-ordens-servicos-form.html');
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
    }
}
