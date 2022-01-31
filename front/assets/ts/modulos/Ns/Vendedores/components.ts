import angular = require('angular');
    export const nsVendedoresDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'NsVendedoresDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
