import angular = require('angular');
    export const nsPaisesDefaultShow =  {
    template: require('./default.form.show.html'),
    controller: 'NsPaisesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
