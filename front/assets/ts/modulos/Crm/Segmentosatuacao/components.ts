import angular = require('angular');
   export const crmSegmentosatuacaoDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmSegmentosatuacaoDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmSegmentosatuacaoDefault= {
    template: require('./default.form.html'),
    controller: 'CrmSegmentosatuacaoDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
