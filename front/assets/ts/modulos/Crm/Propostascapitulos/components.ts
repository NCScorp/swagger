import angular = require('angular');
   export const crmPropostascapitulosDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmPropostascapitulosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
