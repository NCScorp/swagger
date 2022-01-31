import angular = require('angular');
    export const nsAdvertenciasShowShow= {
    template: require('./show.form.show.html'),
    controller: 'NsAdvertenciasShowShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsAdvertenciasArquivar= {
    template: require('./arquivar.form.html'),
    controller: 'NsAdvertenciasArquivarController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const nsAdvertenciasExcluir= {
    template: require('./excluir.form.html'),
    controller: 'NsAdvertenciasExcluirController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
