import angular = require('angular');
    export const crmVinculosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmVinculosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmVinculosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmVinculosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
