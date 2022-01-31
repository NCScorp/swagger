import angular = require('angular');
    export const crmTemplatespropostasdocumentosDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmTemplatespropostasdocumentosDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmTemplatespropostasdocumentosDefault= {
    template: require('./default.form.html'),
    controller: 'CrmTemplatespropostasdocumentosDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
