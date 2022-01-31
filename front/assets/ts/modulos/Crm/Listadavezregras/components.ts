import angular = require('angular');
 export const crmListadavezregrasDefaultShow = {
    template: require('./default.form.show.html'),
    controller: 'CrmListadavezregrasDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
