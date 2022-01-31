import angular = require('angular');
export const crmListadavezvendedoresitensShowShow = {
    template: require('./show.form.show.html'),
    controller: 'CrmListadavezvendedoresitensShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmListadavezvendedoresitensDefault = {
    template: require('./default.form.html'),
    controller: 'CrmListadavezvendedoresitensDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
