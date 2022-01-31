import angular = require('angular');
    export const crmTemplatescomposicoesfamiliasDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmTemplatescomposicoesfamiliasDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
