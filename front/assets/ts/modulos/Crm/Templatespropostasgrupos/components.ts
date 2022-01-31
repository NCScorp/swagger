import angular = require('angular');
    export const crmTemplatespropostasgruposDefault= {
    template: require('./default.form.html'),
    controller: 'CrmTemplatespropostasgruposDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
