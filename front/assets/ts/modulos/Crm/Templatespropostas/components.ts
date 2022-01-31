import angular = require('angular');
    export const crmTemplatespropostasDefault=  {
    template: require('./default.form.html'),
    controller: 'CrmTemplatespropostasDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
