import angular = require('angular');
   export const financasContasfornecedoresShowShow= {
    template: require('./show.form.show.html'),
    controller: 'FinancasContasfornecedoresShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const financasContasfornecedoresDefault= {
    template: require('./default.form.html'),
    controller: 'FinancasContasfornecedoresDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
