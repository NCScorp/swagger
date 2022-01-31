import angular from "angular";
import { FinancasContasListController } from ".";
import { ContasRoutes } from "./config";
import { FIELDS_FinancasContas } from "./constant";
import { FinancasContas } from "./factory";

export const ContasModule = angular
    .module('ContasModule', [])
    .service('FinancasContas', FinancasContas)
    .controller('FinancasContasListController', FinancasContasListController)
    .constant('FIELDS_FinancasContas',FIELDS_FinancasContas)
    .constant('OPTIONS_FinancasContas', { 'bloqueado': 'bloqueado', })
    .constant('MAXOCCURS_FinancasContas', {})
    .constant('SELECTS_FinancasContas', {})
    .run(['$rootScope', 'FIELDS_FinancasContas', 'OPTIONS_FinancasContas', 'MAXOCCURS_FinancasContas', 'SELECTS_FinancasContas',
        ($rootScope: any, FIELDS_FinancasContas: object, OPTIONS_FinancasContas: object, MAXOCCURS_FinancasContas: object, SELECTS_FinancasContas: object) => {
            $rootScope.OPTIONS_FinancasContas = OPTIONS_FinancasContas;
            $rootScope.MAXOCCURS_FinancasContas = MAXOCCURS_FinancasContas;
            $rootScope.SELECTS_FinancasContas = SELECTS_FinancasContas;
            $rootScope.FIELDS_FinancasContas = FIELDS_FinancasContas;
        }])
    .config(ContasRoutes)
    .name