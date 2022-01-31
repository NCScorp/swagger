import angular = require('angular');
    export class CrmAtcsdadosseguradorasDefault implements angular.IComponentOptions {
    static template= require('./default.form.html');
    static controller= 'CrmAtcsdadosseguradorasDefaultController';
    static bindings= {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
