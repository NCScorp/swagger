import angular = require('angular');
export const crmHistoricospadraoDefaultShow =  {
    template: require('./default.form.show.html'),
    controller: 'CrmHistoricospadraoDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
 export const crmHistoricospadraoDefault =  {
    template: require('./default.form.html'),
    controller: 'CrmHistoricospadraoDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
