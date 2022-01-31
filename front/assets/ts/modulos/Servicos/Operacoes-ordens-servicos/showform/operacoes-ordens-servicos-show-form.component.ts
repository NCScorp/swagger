import * as angular from 'angular';
import { OperacoesOrdensServicosShowFormController } from './operacoes-ordens-servicos-show-form.controller';


export class OperacoesOrdensServicosShowFormComponent implements angular.IComponentOptions {
  static selector = 'operacoesOrdensServicosShowForm';
  static controller = OperacoesOrdensServicosShowFormController;
  static template = require('./operacoes-ordens-servicos-show-form.html');
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