import angular from "angular";
import { NsCnaesListController } from ".";
import { CnaesRoutes } from "./config";
import { FIELDS_NsCnaes } from "./constant";
import { NsCnaes } from "./factory";

export const CnaesModule = angular
    .module('CnaesModule', [])
    .service('NsCnaes', NsCnaes)
    .controller('NsCnaesListController', NsCnaesListController)
    .constant('FIELDS_NsCnaes', FIELDS_NsCnaes)
    .run(['$rootScope', 'FIELDS_NsCnaes',
        ($rootScope: any, FIELDS_NsCnaes: object) => {
            $rootScope.FIELDS_NsCnaes = FIELDS_NsCnaes;
        }])
    .config(CnaesRoutes)
    .name