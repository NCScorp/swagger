import angular = require('angular');
   export const nsClientesdocumentosDefaultShow=  {
    template: require('./default.form.show.html'),
    controller: 'NsClientesdocumentosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
});
   export const nsClientesdocumentosDefault=  {
    template: require('./default.form.html'),
    controller: 'NsClientesdocumentosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
});
