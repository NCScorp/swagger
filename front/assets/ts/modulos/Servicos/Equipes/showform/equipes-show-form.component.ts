import * as angular from 'angular';
import { EquipesShowFormController } from './equipes-show-form.controller';


export class EquipesShowFormComponent implements angular.IComponentOptions {
  static selector = 'equipesShowForm';
  static controller = EquipesShowFormController;
  static template = require('./equipes-show-form.html');
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