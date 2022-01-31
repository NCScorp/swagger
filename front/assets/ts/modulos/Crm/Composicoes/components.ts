import angular = require('angular');
export const crmComposicoesDefaultShow = {
  template: require('./default.form.show.html'),
  controller: 'CrmComposicoesDefaultShowController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export const crmComposicoesDefault = {
  template: require('./default.form.html'),
  controller: 'CrmComposicoesDefaultController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
