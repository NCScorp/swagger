import angular = require('angular');
    export const nsEnderecosShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsEnderecosShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsEnderecosDefault= {
    template: require('./default.form.html'),
    controller: 'NsEnderecosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
