import angular = require('angular');
export const crmTemplatespropostascapituloscomposicoesDefault = {
  template: require('./default.form.html'),
  controller: 'CrmTemplatespropostascapituloscomposicoesDefaultController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
