import angular = require('angular');
    export const crmTiposacionamentosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmTiposacionamentosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmTiposacionamentosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmTiposacionamentosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
