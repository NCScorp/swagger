import angular from "angular";
import { NsFormaspagamentosListController } from ".";
import { FormaspagamentosRoutes } from "./config";
import { FIELDS_NsFormaspagamentos } from "./constant";
import { NsFormaspagamentos } from "./factory";

export const FormaspagamentosModule = angular
    .module('FormaspagamentosModule', [])
    .service('NsFormaspagamentos', NsFormaspagamentos)
    .controller('NsFormaspagamentosListController', NsFormaspagamentosListController)
    .constant('FIELDS_NsFormaspagamentos', FIELDS_NsFormaspagamentos)
    .constant('OPTIONS_NsFormaspagamentos', { 'bloqueada': 'bloqueada', })
    .constant('MAXOCCURS_NsFormaspagamentos', {})
    .constant('SELECTS_NsFormaspagamentos', {})
    .run(['$rootScope', 'FIELDS_NsFormaspagamentos', 'OPTIONS_NsFormaspagamentos', 'MAXOCCURS_NsFormaspagamentos', 'SELECTS_NsFormaspagamentos',
        ($rootScope: any, FIELDS_NsFormaspagamentos: object, OPTIONS_NsFormaspagamentos: object, MAXOCCURS_NsFormaspagamentos: object, SELECTS_NsFormaspagamentos: object) => {
            $rootScope.OPTIONS_NsFormaspagamentos = OPTIONS_NsFormaspagamentos;
            $rootScope.MAXOCCURS_NsFormaspagamentos = MAXOCCURS_NsFormaspagamentos;
            $rootScope.SELECTS_NsFormaspagamentos = SELECTS_NsFormaspagamentos;
            $rootScope.FIELDS_NsFormaspagamentos = FIELDS_NsFormaspagamentos;
        }])
    .config(FormaspagamentosRoutes)
    .name