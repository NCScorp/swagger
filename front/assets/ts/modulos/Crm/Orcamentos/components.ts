import angular = require('angular');
    export const crmOrcamentosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmOrcamentosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
});
    export const crmOrcamentosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmOrcamentosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
});
