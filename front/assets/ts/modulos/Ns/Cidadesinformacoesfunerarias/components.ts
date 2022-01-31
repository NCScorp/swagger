import angular = require('angular');
   export const nsCidadesinformacoesfunerariasViewShow=  {
    template: require('./view.form.show.html'),
    controller: 'NsCidadesinformacoesfunerariasViewShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
   export const nsCidadesinformacoesfunerariasCadastro=  {
    template: require('./cadastro.form.html'),
    controller: 'NsCidadesinformacoesfunerariasCadastroController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
