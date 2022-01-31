import angular = require('angular');
  export const gpFuncoescustosDefault =  {
    template: require('./default.form.html'),
    controller: 'GpFuncoescustosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
