import angular = require('angular');
export const crmFornecedoresenvolvidosDefaultShow = {
    template: require('./default.form.show.html'),
    controller: 'CrmFornecedoresenvolvidosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmFornecedoresenvolvidosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmFornecedoresenvolvidosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
