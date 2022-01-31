import angular = require('angular');
    export const crmNegocioscontatosDefault={
    template: require('./default.form.html'),
    controller: 'CrmNegocioscontatosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
