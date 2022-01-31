import angular = require('angular');
export const crmMalotesdocumentosDefaultShow = {
  template: require('./default.form.show.html'),
  controller: 'CrmMalotesdocumentosDefaultShowController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export const crmMalotesdocumentosDefault = {
  template: require('./default.form.html'),
  controller: 'CrmMalotesdocumentosDefaultController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
