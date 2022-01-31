import * as angular from 'angular';
import { NsSeriesFormController } from './series-form.controller';

export class NsSeriesFormComponent implements angular.IComponentOptions {
  static selector = 'nsSeriesForm';
  static controller = NsSeriesFormController;
  static template = require('./series-form.html');
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
