import angular = require('angular');
    export const nsFornecedoresShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsFornecedoresShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsFornecedoresDefault= {
    template: require('./default.form.html'),
    controller: 'NsFornecedoresDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsFornecedoresSuspender= {
    template: require('./suspender.form.html'),
    controller: 'NsFornecedoresSuspenderController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsFornecedoresFornecedorreativar= {
    template: require('./fornecedorreativar.form.html'),
    controller: 'NsFornecedoresFornecedorreativarController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsFornecedoresFornecedoradvertir= {
    template: require('./fornecedoradvertir.form.html'),
    controller: 'NsFornecedoresFornecedoradvertirController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
