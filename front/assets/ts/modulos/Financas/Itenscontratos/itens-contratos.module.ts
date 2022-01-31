import angular from "angular";
import { FinancasItenscontratosListController } from ".";
import { ItenscontratosRoutes } from "./config";
import { FinancasItenscontratos } from "./factory";

export const ItenscontratosModule = angular
    .module('ItenscontratosModule', [])
    .controller('FinancasItenscontratosListController', FinancasItenscontratosListController)
    .constant('FIELDS_FinancasItenscontratos', [])
    .service('FinancasItenscontratos', FinancasItenscontratos)
    .run(['$rootScope', 'FIELDS_FinancasItenscontratos',
        ($rootScope: any, FIELDS_FinancasItenscontratos: object) => {
            $rootScope.FIELDS_FinancasItenscontratos = FIELDS_FinancasItenscontratos;
        }])
    .config(ItenscontratosRoutes)
    .name