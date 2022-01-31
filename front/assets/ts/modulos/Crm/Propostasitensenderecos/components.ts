import angular = require('angular');
    export const crmPropostasitensenderecosDefault={
    template: require('./default.form.html'),
    controller: 'CrmPropostasitensenderecosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
