import angular = require('angular');
   export const crmResponsabilidadesfinanceirasDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmResponsabilidadesfinanceirasDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const crmResponsabilidadesfinanceirasDefault= {
    template: require('./default.form.html'),
    controller: 'CrmResponsabilidadesfinanceirasDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
