import angular from "angular";
import { NsVendedoresListController } from ".";
import { nsVendedoresDefaultShow } from "./components";
import { VendedoresRoutes } from "./config";
import { FIELDS_NsVendedores } from "./constant";
import { NsVendedoresDefaultShowController } from "./default.form.show";
import { NsVendedores } from "./factory";
import { NsVendedoresFormShowController } from "./show";

export const VendedoresModule = angular
    .module('VendedoresModule', [])
    .controller('NsVendedoresDefaultShowController', NsVendedoresDefaultShowController)
    .service('NsVendedores', NsVendedores)
    .controller('NsVendedoresListController', NsVendedoresListController)
    .controller('NsVendedoresFormShowController', NsVendedoresFormShowController)
    .component('nsVendedoresDefaultShow', nsVendedoresDefaultShow)
    .constant('FIELDS_NsVendedores', FIELDS_NsVendedores)
    .constant('OPTIONS_NsVendedores', { 'vendedor_bloqueado': 'vendedor_bloqueado', 'vendedoremail': 'vendedoremail', })
    .constant('MAXOCCURS_NsVendedores', {})
    .constant('SELECTS_NsVendedores', {})
    .run(['$rootScope', 'FIELDS_NsVendedores', 'OPTIONS_NsVendedores', 'MAXOCCURS_NsVendedores', 'SELECTS_NsVendedores',
        ($rootScope: any, FIELDS_NsVendedores: object, OPTIONS_NsVendedores: object, MAXOCCURS_NsVendedores: object, SELECTS_NsVendedores: object) => {
            $rootScope.OPTIONS_NsVendedores = OPTIONS_NsVendedores;
            $rootScope.MAXOCCURS_NsVendedores = MAXOCCURS_NsVendedores;
            $rootScope.SELECTS_NsVendedores = SELECTS_NsVendedores;
            $rootScope.FIELDS_NsVendedores = FIELDS_NsVendedores;
        }])
    .config(VendedoresRoutes)
    .name