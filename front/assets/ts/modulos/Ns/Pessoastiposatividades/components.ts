import angular = require('angular');
   export const nsPessoastiposatividadesDefaultShow = {
    template: require('./default.form.show.html'),
    controller: 'NsPessoastiposatividadesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const nsPessoastiposatividadesDefault = {
    template: require('./default.form.html'),
    controller: 'NsPessoastiposatividadesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<',
      change: '&'
    }
};
