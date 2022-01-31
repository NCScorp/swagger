import * as angular from 'angular';
import { ServicosTecnicosFormController } from './servicos-tecnicos-form.controller';


export class ServicosTecnicosFormComponent implements angular.IComponentOptions {
  static selector = 'servicosTecnicosForm';
  static controller = ServicosTecnicosFormController;
  static template = require('./servicos-tecnicos-form.html');
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
