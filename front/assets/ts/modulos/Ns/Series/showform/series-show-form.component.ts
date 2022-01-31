import * as angular from 'angular';
import { NsSeriesShowFormController } from './series-show-form.controller';

export class NsSeriesShowFormComponent implements angular.IComponentOptions {
  static selector = 'nsSeriesShowForm';
  static controller = NsSeriesShowFormController;
  static template = require('./series-show-form.html');
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