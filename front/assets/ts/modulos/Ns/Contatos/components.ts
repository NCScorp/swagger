import angular = require('angular');
   export const nsContatosShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsContatosShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const nsContatosDefault= {
    template: require('./default.form.html'),
    controller: 'NsContatosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
