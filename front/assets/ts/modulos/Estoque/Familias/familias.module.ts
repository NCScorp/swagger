import angular from "angular";
import { EstoqueFamiliasListController } from ".";
import { FamiliasRoutes } from "./config";
import { FIELDS_EstoqueFamilias } from "./constant";
import { EstoqueFamilias } from "./factory";

export const FamiliasModule = angular
    .module('FamiliasModule', [])
    .service('EstoqueFamilias', EstoqueFamilias)
    .controller('EstoqueFamiliasListController', EstoqueFamiliasListController)
    .constant('FIELDS_EstoqueFamilias', FIELDS_EstoqueFamilias)
    .run(['$rootScope', 'FIELDS_EstoqueFamilias',
        ($rootScope: any, FIELDS_EstoqueFamilias: object) => {
            $rootScope.FIELDS_EstoqueFamilias = FIELDS_EstoqueFamilias;
        }])
    .config(FamiliasRoutes)
    .name
