import angular = require('angular');
export const crmComposicoesfamiliasDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmComposicoesfamiliasDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
