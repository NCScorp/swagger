import angular = require('angular');
export class CrmAtcstiposdocumentosrequisitantesDefault implements angular.IComponentOptions {
  static template = require('./default.form.html')
  static controller = 'CrmAtcstiposdocumentosrequisitantesDefaultController'
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
