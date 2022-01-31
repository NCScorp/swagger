import angular = require('angular');
    export const gpCustosDefault=  {
    template: require('./default.form.html'),
    controller: 'GpCustosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
