import angular = require('angular');
export class CrmAtcsconfiguracoesdocumentosDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcsconfiguracoesdocumentosDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export class CrmAtcsconfiguracoesdocumentosDefault implements angular.IComponentOptions {

  static template = require('./default.form.html');
  static controller = 'CrmAtcsconfiguracoesdocumentosDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
