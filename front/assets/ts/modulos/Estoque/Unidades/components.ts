import angular = require('angular');
    export const estoqueUnidadesDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'EstoqueUnidadesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
}
    export const estoqueUnidadesDefault= {
    template: require('./default.form.html'),
    controller: 'EstoqueUnidadesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
}
