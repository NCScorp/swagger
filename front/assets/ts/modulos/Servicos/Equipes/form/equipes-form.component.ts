import * as angular from 'angular';
import { EquipesFormController } from './equipes-form.controller';


export class EquipesFormComponent implements angular.IComponentOptions {
  static selector = 'equipesForm';
  static controller = EquipesFormController;
  static template = require('./equipes-form.html');
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