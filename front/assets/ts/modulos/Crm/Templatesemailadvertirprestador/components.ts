import angular = require('angular');
    export const crmTemplatesemailadvertirprestadorDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmTemplatesemailadvertirprestadorDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmTemplatesemailadvertirprestadorDefault= {
    template: require('./default.form.html'),
    controller: 'CrmTemplatesemailadvertirprestadorDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
