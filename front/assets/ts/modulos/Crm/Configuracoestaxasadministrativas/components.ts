import angular = require('angular');
export const crmConfiguracoestaxasadministrativasDefaultShow= {
    template: require('./default.form.show.html'),
    controller: 'CrmConfiguracoestaxasadministrativasDefaultShowController',
    bindings: {
        entity: '<',
        form: '<',
        busy: '=',
        action: '<',
        constructors: '<',
        collection: '<'
    }
};
export const crmConfiguracoestaxasadministrativasDefault= {
    template: require('./default.form.html'),
    controller: 'CrmConfiguracoestaxasadministrativasDefaultController',
    bindings: {
        entity: '<',
        form: '<',
        busy: '=',
        action: '<',
        constructors: '<',
        collection: '<'
    }
};
