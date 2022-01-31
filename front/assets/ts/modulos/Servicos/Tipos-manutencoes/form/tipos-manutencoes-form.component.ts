import * as angular from 'angular';
import { TiposManutencoesFormController } from './tipos-manutencoes-form.controller';

export class TiposManutencoesFormComponent implements angular.IComponentOptions {
  static selector = 'tiposManutencoesForm';
  static controller = TiposManutencoesFormController;
  static template = require('./tipos-manutencoes-form.html');
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
    }
}
