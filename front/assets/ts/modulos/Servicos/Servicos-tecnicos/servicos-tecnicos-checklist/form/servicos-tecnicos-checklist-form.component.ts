import * as angular from 'angular';
import { ServicosTecnicosChecklistFormController } from './servicos-tecnicos-checklist-form.controller';


export class ServicosTecnicosChecklistFormComponent implements angular.IComponentOptions {
  static selector = 'servicosTecnicosChecklistForm';
  static controller = ServicosTecnicosChecklistFormController;
  static template = require('./servicos-tecnicos-checklist-form.html');
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
