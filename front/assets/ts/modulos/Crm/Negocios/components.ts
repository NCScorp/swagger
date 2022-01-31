import angular = require('angular');
    export const crmNegociosDefaultShow =  {
    template: require('./default.form.show.html'),
    controller: 'CrmNegociosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmNegociosDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmNegociosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmNegociosQualificacao =  {
    template: require('./qualificacao.form.html'),
    controller: 'CrmNegociosQualificacaoController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmNegociosDesqualificacao =  {
    template: require('./desqualificacao.form.html'),
    controller: 'CrmNegociosDesqualificacaoController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
