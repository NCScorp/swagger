import angular = require('angular');
    export const crmTemplatescomposicoesfuncoesDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmTemplatescomposicoesfuncoesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
