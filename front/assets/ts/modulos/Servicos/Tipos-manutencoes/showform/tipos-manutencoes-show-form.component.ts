import * as angular from 'angular';
import { TiposManutencoesShowFormController } from './tipos-manutencoes-show-form.controller';

export class TiposManutencoesShowFormComponent implements angular.IComponentOptions {
  static selector = 'tiposManutencoesShowForm';
  static controller = TiposManutencoesShowFormController;
  static template = require('./tipos-manutencoes-show-form.html');
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
    }
}