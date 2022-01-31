import angular from "angular";
import { NsTiposlogradourosListController } from ".";
import { TiposlogradourosRoutes } from "./config";
import { FIELDS_NsTiposlogradouros } from "./constant";
import { NsTiposlogradouros } from "./factory";

export const TiposlogradourosModule = angular
    .module('TiposlogradourosModule', [])
    .service('NsTiposlogradouros', NsTiposlogradouros)
    .controller('NsTiposlogradourosListController', NsTiposlogradourosListController)
    .constant('FIELDS_NsTiposlogradouros', FIELDS_NsTiposlogradouros)
    .constant('OPTIONS_NsTiposlogradouros', { 'tipologradouro': 'tipologradouro', 'descricao': 'descricao', })
    .constant('MAXOCCURS_NsTiposlogradouros', {})
    .constant('SELECTS_NsTiposlogradouros', {})
    .run(['$rootScope', 'FIELDS_NsTiposlogradouros', 'OPTIONS_NsTiposlogradouros', 'MAXOCCURS_NsTiposlogradouros', 'SELECTS_NsTiposlogradouros',
        ($rootScope: any, FIELDS_NsTiposlogradouros: object, OPTIONS_NsTiposlogradouros: object, MAXOCCURS_NsTiposlogradouros: object, SELECTS_NsTiposlogradouros: object) => {
            $rootScope.OPTIONS_NsTiposlogradouros = OPTIONS_NsTiposlogradouros;
            $rootScope.MAXOCCURS_NsTiposlogradouros = MAXOCCURS_NsTiposlogradouros;
            $rootScope.SELECTS_NsTiposlogradouros = SELECTS_NsTiposlogradouros;
            $rootScope.FIELDS_NsTiposlogradouros = FIELDS_NsTiposlogradouros;
        }])
    .config(TiposlogradourosRoutes)
    .name