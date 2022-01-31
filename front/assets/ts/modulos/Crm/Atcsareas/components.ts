import angular = require('angular');

export class CrmAtcsareasDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcsareasDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class CrmAtcsareasDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcsareasDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
