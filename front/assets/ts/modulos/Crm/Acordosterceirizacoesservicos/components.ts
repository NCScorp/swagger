import angular = require('angular');

export class crmAcordosterceirizacoesservicosDefaultShow implements angular.IComponentOptions {
    static template = require('./default.form.show.html');
    static controller = 'CrmAcordosterceirizacoesservicosDefaultShowController';
    static bindings = {
        entity: '<',
        form: '<',
        busy: '=',
        action: '<',
        constructors: '<',
        collection: '<'
    }
};

export class crmAcordosterceirizacoesservicosDefault implements angular.IComponentOptions {
    static template = require('./default.form.html');
    static controller = 'CrmAcordosterceirizacoesservicosDefaultController';
    static bindings = {
        entity: '<',
        form: '<',
        busy: '=',
        action: '<',
        constructors: '<',
        collection: '<'
    }
};