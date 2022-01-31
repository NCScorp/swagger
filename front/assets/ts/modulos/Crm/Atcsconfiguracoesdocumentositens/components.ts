import angular = require('angular');
export class CrmAtcsconfiguracoesdocumentositensDefaultShow implements angular.IComponentOptions {
  static template = require('./default.form.show.html');
  static controller = 'CrmAtcsconfiguracoesdocumentositensDefaultShowController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};

export class CrmAtcsconfiguracoesdocumentositensDefault implements angular.IComponentOptions {
  static template = require('./default.form.html');
  static controller = 'CrmAtcsconfiguracoesdocumentositensDefaultController';
  static bindings = {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
