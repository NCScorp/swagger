import * as angular from 'angular';
import { ListagemMateriaisFormController } from './listagem-materiais-form.controller';

export class ListagemMateriaisFormComponent implements angular.IComponentOptions {
  static selector = 'listagemMateriaisForm';
  static controller = ListagemMateriaisFormController;
  static template = require('./listagem-materiais-form.html');
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
