import angular from "angular";
import { ServicosItensdefaturamentoListController } from ".";
import { ItensdefaturamentoRoutes } from "./config";
import { FIELDS_ServicosItensdefaturamento } from "./constant";
import { ServicosItensdefaturamento } from "./factory";

export const ItensdefaturamentoModule = angular
    .module('ItensdefaturamentoModule', [])
    .service('ServicosItensdefaturamento', ServicosItensdefaturamento)
    .controller('ServicosItensdefaturamentoListController', ServicosItensdefaturamentoListController)
    .constant('OPTIONS_ServicosItensdefaturamento', { 'bloqueado': 'bloqueado', })
    .constant('FIELDS_ServicosItensdefaturamento', FIELDS_ServicosItensdefaturamento)
    .constant('MAXOCCURS_ServicosItensdefaturamento', {})
    .constant('SELECTS_ServicosItensdefaturamento', {})
    .run(['$rootScope', 'FIELDS_ServicosItensdefaturamento', 'OPTIONS_ServicosItensdefaturamento', 'MAXOCCURS_ServicosItensdefaturamento', 'SELECTS_ServicosItensdefaturamento',
        ($rootScope: any, FIELDS_ServicosItensdefaturamento: object, OPTIONS_ServicosItensdefaturamento: object, MAXOCCURS_ServicosItensdefaturamento: object, SELECTS_ServicosItensdefaturamento: object) => {
            $rootScope.OPTIONS_ServicosItensdefaturamento = OPTIONS_ServicosItensdefaturamento;
            $rootScope.MAXOCCURS_ServicosItensdefaturamento = MAXOCCURS_ServicosItensdefaturamento;
            $rootScope.SELECTS_ServicosItensdefaturamento = SELECTS_ServicosItensdefaturamento;
            $rootScope.FIELDS_ServicosItensdefaturamento = FIELDS_ServicosItensdefaturamento;
        }])
    .config(ItensdefaturamentoRoutes)
    .name