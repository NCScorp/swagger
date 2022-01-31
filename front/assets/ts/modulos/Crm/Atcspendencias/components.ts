import angular = require('angular');

export class CrmAtcspendenciasDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcspendenciasDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export class CrmAtcspendenciasDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcspendenciasDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export class crmAtcspendenciasEdit implements angular.IComponentOptions {
  static template = require('./edit.form.html');
  static controller = 'CrmAtcspendenciasEditController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
