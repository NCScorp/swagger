import * as angular from 'angular';

export const HomeRouting = ['$stateProvider', '$urlRouterProvider', ($stateProvider: angular.ui.IStateProvider, $urlRouterProvider: any) => {
    let resolveFilter = {};

    $stateProvider
        .state('home', {
            url: '/home',
            resolve: resolveFilter,
            template: require('./home.html'),
            controller: 'homeController',
            controllerAs: 'hm_cntrllr',
            reloadOnSearch: false
        });

    $urlRouterProvider.otherwise('/home');
}];

