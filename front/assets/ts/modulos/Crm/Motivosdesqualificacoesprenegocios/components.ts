import angular = require('angular');
  export const crmMotivosdesqualificacoesprenegociosDefaultShow=  {
    template: require('./default.form.show.html'),
    controller: 'CrmMotivosdesqualificacoesprenegociosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
} ;
