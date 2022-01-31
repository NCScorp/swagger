import angular = require('angular');
  export const nsPessoasLogosDefault= {
    template: require('./default.form.html'),
    controller: 'NsPessoasLogosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
