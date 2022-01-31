import angular = require('angular');
    export const nsCnaestiposatividadesDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'NsCnaestiposatividadesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsCnaestiposatividadesDefault= {
    template: require('./default.form.html'),
    controller: 'NsCnaestiposatividadesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
