import angular = require('angular');
   export const nsTiposdocumentosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'NsTiposdocumentosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const nsTiposdocumentosDefault= {
    template: require('./default.form.html'),
    controller: 'NsTiposdocumentosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
