import angular = require('angular');
    export const crmListadavezvendedoresShowShow = {
    template: require('./show.form.show.html'),
    controller: 'CrmListadavezvendedoresShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};

 export const crmListadavezvendedoresDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmListadavezvendedoresDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
