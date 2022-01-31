import angular = require('angular');
    export const crmSituacoesprenegociosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmSituacoesprenegociosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmSituacoesprenegociosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmSituacoesprenegociosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
