import angular = require('angular');
export const nsTiposatividadesDefaultShow=  {
    template: require('./default.form.show.html'),
    controller: 'NsTiposatividadesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
export const nsTiposatividadesDefault=  {
    template: require('./default.form.html'),
    controller: 'NsTiposatividadesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
