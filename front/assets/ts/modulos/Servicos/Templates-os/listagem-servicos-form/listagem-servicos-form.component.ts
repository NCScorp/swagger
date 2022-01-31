import * as angular from 'angular';
import { ListagemServicosFormController } from './listagem-servicos-form.controller';

export class ListagemServicosFormComponent implements angular.IComponentOptions {
  static selector = 'listagemServicosForm';
  static controller = ListagemServicosFormController;
  static template = require('./listagem-servicos-form.html');
  static transclude = true;
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<',
    disableAction: '<'
  }
}
