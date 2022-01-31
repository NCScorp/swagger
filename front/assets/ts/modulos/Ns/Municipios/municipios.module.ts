import angular from "angular";
import { config } from "process";
import { NsMunicipiosListController } from ".";
import { nsMunicipiosDefaultShow } from "./components";
import { MunicipiosRoutes } from "./config";
import { FIELDS_NsMunicipios } from "./constant";
import { NsMunicipiosDefaultShowController } from "./default.form.show";
import { NsMunicipios } from "./factory";
import { NsMunicipiosFormShowController } from "./show";

export const MunicipiosModule = angular
    .module('MunicipiosModule', [])
    .controller('NsMunicipiosDefaultShowController', NsMunicipiosDefaultShowController)
    .service('NsMunicipios', NsMunicipios)
    .controller('NsMunicipiosListController', NsMunicipiosListController)
    .controller('NsMunicipiosFormShowController', NsMunicipiosFormShowController)
    .component('nsMunicipiosDefaultShow', nsMunicipiosDefaultShow)
    .constant('FIELDS_NsMunicipios', FIELDS_NsMunicipios)
    .constant('OPTIONS_NsMunicipios', { 'nome': 'nome', 'uf': 'uf', })
    .constant('MAXOCCURS_NsMunicipios', {})
    .constant('SELECTS_NsMunicipios', {})
    .run(['$rootScope', 'FIELDS_NsMunicipios', 'OPTIONS_NsMunicipios', 'MAXOCCURS_NsMunicipios', 'SELECTS_NsMunicipios',
        ($rootScope: any, FIELDS_NsMunicipios: object, OPTIONS_NsMunicipios: object, MAXOCCURS_NsMunicipios: object, SELECTS_NsMunicipios: object) => {
            $rootScope.OPTIONS_NsMunicipios = OPTIONS_NsMunicipios;
            $rootScope.MAXOCCURS_NsMunicipios = MAXOCCURS_NsMunicipios;
            $rootScope.SELECTS_NsMunicipios = SELECTS_NsMunicipios;
            $rootScope.FIELDS_NsMunicipios = FIELDS_NsMunicipios;
        }])
    .config(MunicipiosRoutes)
    .name