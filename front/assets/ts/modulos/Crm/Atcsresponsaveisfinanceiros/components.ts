import angular = require('angular');
export class CrmAtcsresponsaveisfinanceirosDefault implements angular.IComponentOptions {
  static template = require('./default.form.html')
  static controller = 'CrmAtcsresponsaveisfinanceirosDefaultController'
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
