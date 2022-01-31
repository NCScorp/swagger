import angular = require('angular');
 export const nsMunicipiosDefaultShow=  {
    template: require('./default.form.show.html'),
    controller: 'NsMunicipiosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
