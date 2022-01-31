import * as angular from 'angular';
import { OperacoesOrdensServicosFormController } from './operacoes-ordens-servicos-form.controller';


export class OperacoesOrdensServicosFormComponent implements angular.IComponentOptions {
  static selector = 'operacoesOrdensServicosForm';
  static controller = OperacoesOrdensServicosFormController;
  static template = require('./operacoes-ordens-servicos-form.html');
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