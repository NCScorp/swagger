import angular = require('angular');
    export const gpFuncoesDefaultShow=  {
    template: require('./default.form.show.html'),
    controller: 'GpFuncoesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const gpFuncoesDefault=  {
    template: require('./default.form.html'),
    controller: 'GpFuncoesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
