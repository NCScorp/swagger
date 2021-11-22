import * as angular from 'angular';

export const ContratosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {
    let resolveFilter = {};

    $stateProvider
        .state('contratos', {
            url: '/contratos?q',
            resolve: resolveFilter,
            template: require('./index/contratos-index.html'),
            controller: 'contratosIndexController',
            controllerAs: 'ctt_lst_cntrllr',
            reloadOnSearch: false
        });
}];

