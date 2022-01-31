import angular = require('angular');
   export const nsFollowupsnegociosDefault=  {
    template: require('./default.form.html'),
    controller: 'NsFollowupsnegociosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
