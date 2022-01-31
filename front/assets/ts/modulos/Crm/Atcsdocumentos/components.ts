import angular = require('angular');
export class CrmAtcsdocumentosDefaultShow implements angular.IComponentOptions {
    static template= require('./default.form.show.html');
    static controller= 'CrmAtcsdocumentosDefaultShowController';
    static bindings= {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export class CrmAtcsdocumentosDefault implements angular.IComponentOptions{
    static template= require('./default.form.html');
    static controller= 'CrmAtcsdocumentosDefaultController';
    static bindings= {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
