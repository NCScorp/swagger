import angular from "angular";
import { CrmHistoricoatcsListController } from ".";
import { HistoricoatcsRoutes } from "./config";
import { FIELDS_CrmHistoricoatcs } from "./constant";
import { CrmHistoricoatcs } from "./factory";

export const HistoricoatcsModule = angular
    .module('HistoricoatcsModule', [])
    .controller('CrmHistoricoatcsListController', CrmHistoricoatcsListController)
    .constant('FIELDS_CrmHistoricoatcs', FIELDS_CrmHistoricoatcs)
    .constant('OPTIONS_CrmHistoricoatcs', { 'secao': 'secao', })
    .constant('MAXOCCURS_CrmHistoricoatcs', {})
    .constant('SELECTS_CrmHistoricoatcs', {})
    .service('CrmHistoricoatcs', CrmHistoricoatcs)
    .run(['$rootScope', 'FIELDS_CrmHistoricoatcs', 'OPTIONS_CrmHistoricoatcs', 'MAXOCCURS_CrmHistoricoatcs', 'SELECTS_CrmHistoricoatcs',
        ($rootScope: any, FIELDS_CrmHistoricoatcs: object, OPTIONS_CrmHistoricoatcs: object, MAXOCCURS_CrmHistoricoatcs: object, SELECTS_CrmHistoricoatcs: object) => {
            $rootScope.OPTIONS_CrmHistoricoatcs = OPTIONS_CrmHistoricoatcs;
            $rootScope.MAXOCCURS_CrmHistoricoatcs = MAXOCCURS_CrmHistoricoatcs;
            $rootScope.SELECTS_CrmHistoricoatcs = SELECTS_CrmHistoricoatcs;
            $rootScope.FIELDS_CrmHistoricoatcs = FIELDS_CrmHistoricoatcs;
        }])
    .config(HistoricoatcsRoutes)
    .name
