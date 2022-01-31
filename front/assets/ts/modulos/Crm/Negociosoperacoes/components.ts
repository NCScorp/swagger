import angular = require('angular');
    export const crmNegociosoperacoesDefaultShow =  {
    template: require('./default.form.show.html'),
    controller: 'CrmNegociosoperacoesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
