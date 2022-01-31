import angular from "angular";
import { FinancasContratosListController } from ".";
import { ContratosRoutes } from "./config";
import { FinancasContratos } from "./factory";

export const ContratosModule = angular
    .module('ContratosModule', [])
    .constant('FIELDS_FinancasContratos', [])
    .service('FinancasContratos', FinancasContratos)
    .controller('FinancasContratosListController', FinancasContratosListController)
    .run(['$rootScope', 'FIELDS_FinancasContratos',
        ($rootScope: any, FIELDS_FinancasContratos: object) => {
            $rootScope.FIELDS_FinancasContratos = FIELDS_FinancasContratos;
        }])
    .config(ContratosRoutes)
    .name