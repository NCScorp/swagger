import angular = require('angular');
    export const nsPrioridadesShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsPrioridadesShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
  export const nsPrioridadesDefault= {
    template: require('./default.form.html'),
    controller: 'NsPrioridadesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};

  export const nsPrioridadesDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'NsPrioridadesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};