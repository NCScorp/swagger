import angular = require('angular');
   export const nsProspectsDefault=  {
    template: require('./default.form.html'),
    controller: 'NsProspectsDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
