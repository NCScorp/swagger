import angular from "angular";
import { NsjAuth, NsjHttpInterceptorService } from '@nsj/core';

class HttpInterceptorService extends NsjHttpInterceptorService {
    responseError = function (rejection) {
        if (rejection.status === 401 || (rejection.status === 400 && rejection.data.code === 493)) {
            NsjAuth.authService.logout();
        }
        return Promise.reject(rejection);
    }
}

export const appRoutes = ['$locationProvider', '$urlRouterProvider', '$httpProvider',
    (
        $locationProvider: angular.ILocationProvider,
        $urlRouterProvider: angular.ui.IUrlRouterProvider,
        $httpProvider: angular.IHttpProvider) => {
        $httpProvider.interceptors.push(HttpInterceptorService.Factory);
        $locationProvider.html5Mode(true);
        $urlRouterProvider.otherwise('crm/dashboard-atendimentos-pendencias');
    }
];
