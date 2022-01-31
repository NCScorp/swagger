import * as angular from 'angular';
import { ServicosTecnicosShowFormController } from './servicos-tecnicos-show-form.controller';

export class ServicosTecnicosShowFormComponent implements angular.IComponentOptions {
  static selector = 'servicosTecnicosShowForm';
  static controller = ServicosTecnicosShowFormController;
  static template = require('./servicos-tecnicos-show-form.html');
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
