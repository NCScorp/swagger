import angular = require('angular');
   export const nsTelefonesShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsTelefonesShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const nsTelefonesDefault= {
    template: require('./default.form.html'),
    controller: 'NsTelefonesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
