import angular = require('angular');
   export const crmPropostasitensDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmPropostasitensDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmPropostasitensDefault= {
    template: require('./default.form.html'),
    controller: 'CrmPropostasitensDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmPropostasitensEdicao= {
    template: require('./edicao.form.html'),
    controller: 'CrmPropostasitensEdicaoController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmPropostasitensPropostasitensvincularfornecedor= {
    template: require('./propostasItensVincularFornecedor.form.html'),
    controller: 'CrmPropostasitensPropostasitensvincularfornecedorController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmPropostasitensPropostasitensfornecedorescolhacliente= {
    template: require('./propostasitensfornecedorescolhacliente.form.html'),
    controller: 'CrmPropostasitensPropostasitensfornecedorescolhaclienteController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
