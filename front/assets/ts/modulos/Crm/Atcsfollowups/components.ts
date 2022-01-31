import angular = require('angular');
export class CrmAtcsfollowupsDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcsfollowupsDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class CrmAtcsfollowupsDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcsfollowupsDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
