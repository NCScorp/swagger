import angular = require('angular');
export const crmFornecedoresenvolvidosanexosDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmFornecedoresenvolvidosanexosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
