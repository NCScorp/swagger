import angular = require('angular');
export const crmPainelMarketing = {
    template: require('./view.html'),
    controller: 'CrmPainelMarketingController',
    bindings: {
        busy: '=',
        ctrlPainel: '='
    }
};