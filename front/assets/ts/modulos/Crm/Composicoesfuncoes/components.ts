import angular = require('angular');
export const crmComposicoesfuncoesDefault = {
    template: require('./default.form.html'),
    controller: 'CrmComposicoesfuncoesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
