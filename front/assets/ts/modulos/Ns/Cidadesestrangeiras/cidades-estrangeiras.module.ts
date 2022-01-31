import angular from "angular";
import { NsCidadesestrangeirasListController } from ".";
import { CidadesestrangeirasRoutes } from "./config";
import { FIELDS_NsCidadesestrangeiras } from "./constant";
import { NsCidadesestrangeiras } from "./factory";

export const CidadesestrangeirasModule = angular
    .module('CidadesestrangeirasModule', [])
    .service('NsCidadesestrangeiras', NsCidadesestrangeiras)
    .controller('NsCidadesestrangeirasListController', NsCidadesestrangeirasListController)
    .constant('FIELDS_NsCidadesestrangeiras', FIELDS_NsCidadesestrangeiras)
    .constant('OPTIONS_NsCidadesestrangeiras', { 'nome': 'nome', })
    .constant('MAXOCCURS_NsCidadesestrangeiras', {})
    .constant('SELECTS_NsCidadesestrangeiras', {})
    .run(['$rootScope', 'FIELDS_NsCidadesestrangeiras', 'OPTIONS_NsCidadesestrangeiras', 'MAXOCCURS_NsCidadesestrangeiras', 'SELECTS_NsCidadesestrangeiras',
        ($rootScope: any, FIELDS_NsCidadesestrangeiras: object, OPTIONS_NsCidadesestrangeiras: object, MAXOCCURS_NsCidadesestrangeiras: object, SELECTS_NsCidadesestrangeiras: object) => {
            $rootScope.OPTIONS_NsCidadesestrangeiras = OPTIONS_NsCidadesestrangeiras;
            $rootScope.MAXOCCURS_NsCidadesestrangeiras = MAXOCCURS_NsCidadesestrangeiras;
            $rootScope.SELECTS_NsCidadesestrangeiras = SELECTS_NsCidadesestrangeiras;
            $rootScope.FIELDS_NsCidadesestrangeiras = FIELDS_NsCidadesestrangeiras;
        }])
    .config(CidadesestrangeirasRoutes)
    .name