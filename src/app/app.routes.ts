import { NsjHttpInterceptorService } from './core/nsj-core';

export const appRoutes = ['$locationProvider', '$urlRouterProvider', '$httpProvider',
    ($locationProvider: angular.ILocationProvider,
        $urlRouterProvider: angular.ui.IUrlRouterProvider,
        $httpProvider: angular.IHttpProvider) => {
        $httpProvider.interceptors.push(NsjHttpInterceptorService.Factory);
        $locationProvider.html5Mode(true);
        $urlRouterProvider.otherwise('/home');
    }];
