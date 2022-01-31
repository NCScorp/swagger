import angular = require('angular');
    export const nsDocumentosnecessariosDefault= {
    template: require('./default.form.html'),
    controller: 'NsDocumentosnecessariosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
