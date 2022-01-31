import angular = require('angular');
    export const crmTemplatespropostascapitulosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmTemplatespropostascapitulosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
