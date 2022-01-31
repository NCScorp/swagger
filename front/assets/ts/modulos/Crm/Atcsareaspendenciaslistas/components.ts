import angular = require('angular');

export class CrmAtcsareaspendenciaslistasDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcsareaspendenciaslistasDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export class CrmAtcsareaspendenciaslistasDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcsareaspendenciaslistasDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
