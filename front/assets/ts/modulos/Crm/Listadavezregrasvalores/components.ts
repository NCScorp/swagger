import angular = require('angular');
  export const crmListadavezregrasvaloresDefaultShow =  {
    template: require('./default.form.show.html'),
    controller: 'CrmListadavezregrasvaloresDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
