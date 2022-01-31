import angular = require('angular');
    export const crmResponsabilidadesfinanceirasvaloresDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmResponsabilidadesfinanceirasvaloresDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmResponsabilidadesfinanceirasvaloresDefault= {
    template: require('./default.form.html'),
    controller: 'CrmResponsabilidadesfinanceirasvaloresDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
