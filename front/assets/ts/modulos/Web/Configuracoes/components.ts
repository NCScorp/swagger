import angular = require('angular');
 export const webConfiguracoesDefaultShow = {
    template: require('./default.form.show.html'),
    controller: 'WebConfiguracoesDefaultShowController',
    bindings: {
      entity: '<',
      form: '<',
      busy: '=',
      action: '<',
      constructors: '<',
      collection: '<'
    }
};
