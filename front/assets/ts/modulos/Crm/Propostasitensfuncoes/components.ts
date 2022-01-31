import angular = require('angular');
export const crmPropostasitensfuncoesDefaultShow = {
  template: require('./default.form.show.html'),
  controller: 'CrmPropostasitensfuncoesDefaultShowController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export const crmPropostasitensfuncoesDefault = {
  template: require('./default.form.html'),
  controller: 'CrmPropostasitensfuncoesDefaultController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export const crmPropostasitensfuncoesEdicao = {
  template: require('./edicao.form.html'),
  controller: 'CrmPropostasitensfuncoesEdicaoController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
