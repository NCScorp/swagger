import angular = require('angular');
    export const crmPromocoesleadsDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmPromocoesleadsDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmPromocoesleadsDefault= {
    template: require('./default.form.html'),
    controller: 'CrmPromocoesleadsDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
