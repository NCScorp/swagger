import angular = require('angular');
    export const crmMalotesVisualizacaoShow =  {
    template: require('./visualizacao.form.show.html'),
    controller: 'CrmMalotesVisualizacaoShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
export const crmMalotesDefault  =  {
    template: require('./default.form.html'),
    controller: 'CrmMalotesDefaultController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const crmMalotesAprovar =  {
    template: require('./aprovar.form.html'),
    controller: 'CrmMalotesAprovarController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
export const crmMalotesEnviar = {
    template: require('./enviar.form.html'),
    controller: 'CrmMalotesEnviarController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
    export const  crmMalotesEditarenvio = {
    template: require('./editarenvio.form.html'),
    controller: 'CrmMalotesEditarenvioController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
