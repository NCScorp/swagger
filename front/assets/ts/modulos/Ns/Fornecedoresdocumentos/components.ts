import angular = require('angular');
    export const nsFornecedoresdocumentosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'NsFornecedoresdocumentosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsFornecedoresdocumentosDefault= {
    template: require('./default.form.html'),
    controller: 'NsFornecedoresdocumentosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
