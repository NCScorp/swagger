import angular from "angular";
import { NsLayoutsdocumentosimpressosListController } from ".";
import { nsLayoutsdocumentosimpressosDefaultShow } from "./components";
import { LayoutsdocumentosimpressosRoutes } from "./config";
import { FIELDS_NsLayoutsdocumentosimpressos } from "./constant";
import { NsLayoutsdocumentosimpressosDefaultShowController } from "./default.form.show";
import { NsLayoutsdocumentosimpressos } from "./factory";
import { NsLayoutsdocumentosimpressosFormShowController } from "./show";

export const LayoutsdocumentosimpressosModule = angular
    .module('LayoutsdocumentosimpressosModule', [])
    .controller('NsLayoutsdocumentosimpressosDefaultShowController', NsLayoutsdocumentosimpressosDefaultShowController)
    .service('NsLayoutsdocumentosimpressos', NsLayoutsdocumentosimpressos)
    .controller('NsLayoutsdocumentosimpressosListController', NsLayoutsdocumentosimpressosListController)
    .controller('NsLayoutsdocumentosimpressosFormShowController', NsLayoutsdocumentosimpressosFormShowController)
    .component('nsLayoutsdocumentosimpressosDefaultShow', nsLayoutsdocumentosimpressosDefaultShow)
    .constant('FIELDS_NsLayoutsdocumentosimpressos', FIELDS_NsLayoutsdocumentosimpressos)
    .constant('OPTIONS_NsLayoutsdocumentosimpressos', { 'sistema': 'sistema', 'nomedocumento': 'nomedocumento', })
    .constant('MAXOCCURS_NsLayoutsdocumentosimpressos', { 'sistema': 1, 'nomedocumento': 1, })
    .constant('SELECTS_NsLayoutsdocumentosimpressos', {})
    .run(['$rootScope', 'FIELDS_NsLayoutsdocumentosimpressos', 'OPTIONS_NsLayoutsdocumentosimpressos', 'MAXOCCURS_NsLayoutsdocumentosimpressos', 'SELECTS_NsLayoutsdocumentosimpressos',
        ($rootScope: any, FIELDS_NsLayoutsdocumentosimpressos: object, OPTIONS_NsLayoutsdocumentosimpressos: object, MAXOCCURS_NsLayoutsdocumentosimpressos: object, SELECTS_NsLayoutsdocumentosimpressos: object) => {
            $rootScope.OPTIONS_NsLayoutsdocumentosimpressos = OPTIONS_NsLayoutsdocumentosimpressos;
            $rootScope.MAXOCCURS_NsLayoutsdocumentosimpressos = MAXOCCURS_NsLayoutsdocumentosimpressos;
            $rootScope.SELECTS_NsLayoutsdocumentosimpressos = SELECTS_NsLayoutsdocumentosimpressos;
            $rootScope.FIELDS_NsLayoutsdocumentosimpressos = FIELDS_NsLayoutsdocumentosimpressos;
        }])
    .config(LayoutsdocumentosimpressosRoutes)
    .name