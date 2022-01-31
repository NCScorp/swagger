import angular from "angular";
import { NsPaisesListController } from ".";
import { nsPaisesDefaultShow } from "./components";
import { PaisesRoutes } from "./config";
import { FIELDS_NsPaises } from "./constant";
import { NsPaisesDefaultShowController } from "./default.form.show";
import { NsPaises } from "./factory";
import { NsPaisesFormShowController } from "./show";

export const PaisesModule = angular
    .module('PaisesModule', [])
    .controller('NsPaisesDefaultShowController', NsPaisesDefaultShowController)
    .controller('NsPaisesListController', NsPaisesListController)
    .controller('NsPaisesFormShowController', NsPaisesFormShowController)
    .service('NsPaises', NsPaises)
    .component('nsPaisesDefaultShow', nsPaisesDefaultShow)
    .constant('FIELDS_NsPaises', FIELDS_NsPaises)
    .constant('OPTIONS_NsPaises', { 'nome': 'nome', })
    .constant('MAXOCCURS_NsPaises', {})
    .constant('SELECTS_NsPaises', {})
    .run(['$rootScope', 'FIELDS_NsPaises', 'OPTIONS_NsPaises', 'MAXOCCURS_NsPaises', 'SELECTS_NsPaises',
        ($rootScope: any, FIELDS_NsPaises: object, OPTIONS_NsPaises: object, MAXOCCURS_NsPaises: object, SELECTS_NsPaises: object) => {
            $rootScope.OPTIONS_NsPaises = OPTIONS_NsPaises;
            $rootScope.MAXOCCURS_NsPaises = MAXOCCURS_NsPaises;
            $rootScope.SELECTS_NsPaises = SELECTS_NsPaises;
            $rootScope.FIELDS_NsPaises = FIELDS_NsPaises;
        }])
    .config(PaisesRoutes)
    .name