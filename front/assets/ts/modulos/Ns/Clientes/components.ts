import angular = require('angular');
    export const nsClientesShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsClientesShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsClientesDefault= {
    template: require('./default.form.html'),
    controller: 'NsClientesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
