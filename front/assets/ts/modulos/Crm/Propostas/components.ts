import angular = require('angular');
    export const crmPropostasDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmPropostasDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
});
    export const crmPropostasDefault= {
    template: require('./default.form.html'),
    controller: 'CrmPropostasDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
});
