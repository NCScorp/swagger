import angular = require('angular');
export const crmMidiasDefaultShow = {
  template: require('./default.form.show.html'),
  controller: 'CrmMidiasDefaultShowController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
export const crmMidiasDefault = {
  template: require('./default.form.html'),
  controller: 'CrmMidiasDefaultController',
  bindings: {
    entity: '<',
    form: '<',
    busy: '=',
    action: '<',
    constructors: '<',
    collection: '<'
  }
};
