import angular = require('angular');
  export const crmPropostasitensfamiliasDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmPropostasitensfamiliasDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
  export const crmPropostasitensfamiliasDefault= {
    template: require('./default.form.html'),
    controller: 'CrmPropostasitensfamiliasDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
  export const crmPropostasitensfamiliasEdicao= {
    template: require('./edicao.form.html'),
    controller: 'CrmPropostasitensfamiliasEdicaoController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
