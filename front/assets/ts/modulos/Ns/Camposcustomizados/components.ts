import angular = require('angular');
   export const nsCamposcustomizadosDefault= {
    template: require('./default.form.html'),
    controller: 'NsCamposcustomizadosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
