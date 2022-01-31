import * as angular from 'angular';
import { EquipesTecnicosFormController } from './equipes-tecnicos-form.controller';

export class EquipesTecnicosFormComponent implements angular.IComponentOptions {
  static selector = 'equipesTecnicosForm';
  static controller = EquipesTecnicosFormController;
  static template = require('./equipes-tecnicos-form.html');
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