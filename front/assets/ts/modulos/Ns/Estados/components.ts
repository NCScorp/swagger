import angular = require('angular');
    export const nsEstadosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'NsEstadosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
