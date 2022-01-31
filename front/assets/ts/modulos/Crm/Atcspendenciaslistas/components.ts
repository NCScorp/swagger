import angular = require('angular');
export class CrmAtcspendenciaslistasDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcspendenciaslistasDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export class CrmAtcspendenciaslistasDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcspendenciaslistasDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
