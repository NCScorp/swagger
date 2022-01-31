import angular from "angular";
import { FinancasBancosListController } from ".";
import { financasBancosDefaultShow } from "./components";
import { BancosRoutes } from "./config";
import { FIELDS_FinancasBancos } from "./constant";
import { FinancasBancosDefaultShowController } from "./default.form.show";
import { FinancasBancos } from "./factory";
import { FinancasBancosFormShowController } from "./show";

export const BancosModule = angular
    .module('BancosModule', [])
    .service('FinancasBancos', FinancasBancos)
    .controller('FinancasBancosListController', FinancasBancosListController)
    .controller('FinancasBancosFormShowController', FinancasBancosFormShowController)
    .component('financasBancosDefaultShow', financasBancosDefaultShow)
    .constant('FIELDS_FinancasBancos', FIELDS_FinancasBancos)
    .controller('FinancasBancosDefaultShowController', FinancasBancosDefaultShowController)
    .constant('OPTIONS_FinancasBancos', { 'numero': 'numero', 'nome': 'nome', })
    .constant('MAXOCCURS_FinancasBancos', {})
    .constant('SELECTS_FinancasBancos', {})
    .run(['$rootScope', 'FIELDS_FinancasBancos', 'OPTIONS_FinancasBancos', 'MAXOCCURS_FinancasBancos', 'SELECTS_FinancasBancos',
        ($rootScope: any, FIELDS_FinancasBancos: object, OPTIONS_FinancasBancos: object, MAXOCCURS_FinancasBancos: object, SELECTS_FinancasBancos: object) => {
            $rootScope.OPTIONS_FinancasBancos = OPTIONS_FinancasBancos;
            $rootScope.MAXOCCURS_FinancasBancos = MAXOCCURS_FinancasBancos;
            $rootScope.SELECTS_FinancasBancos = SELECTS_FinancasBancos;
            $rootScope.FIELDS_FinancasBancos = FIELDS_FinancasBancos;
        }])
    .config(BancosRoutes)
    .name
