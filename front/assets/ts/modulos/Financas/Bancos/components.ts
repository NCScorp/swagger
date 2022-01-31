import angular = require('angular');
    export const financasBancosDefaultShow =  {
    template: require('./default.form.show.html'),
    controller: 'FinancasBancosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
