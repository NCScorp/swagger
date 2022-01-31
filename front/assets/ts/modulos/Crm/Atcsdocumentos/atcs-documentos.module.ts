import angular from "angular";
import { CrmAtcsdocumentosListController } from ".";
import { CrmAtcsdocumentosDefaultShow, CrmAtcsdocumentosDefault } from "./components";
import { AtcsdocumentosRoutes } from "./config";
import { FIELDS_CrmAtcsdocumentos, OPTIONS_CrmAtcsdocumentos, MAXOCCURS_CrmAtcsdocumentos, SELECT_Status_CrmAtcsdocumentos, SELECTS_CrmAtcsdocumentos } from "./constant";
import { CrmAtcsdocumentosDefaultController } from "./default.form";
import { CrmAtcsdocumentosDefaultShowController } from "./default.form.show";
import { CrmAtcsdocumentosFormController } from "./edit";
import { CrmAtcsdocumentos } from "./factory";
import { CrmAtcsdocumentosFormNewController } from "./new";
import { CrmAtcsdocumentosFormShowController } from "./show";

export const AtcsdocumentosModule = angular
    .module('AtcsdocumentosModule', [])
    .controller('CrmAtcsdocumentosDefaultShowController', CrmAtcsdocumentosDefaultShowController)
    .controller('CrmAtcsdocumentosDefaultController', CrmAtcsdocumentosDefaultController)
    .controller('CrmAtcsdocumentosFormController', CrmAtcsdocumentosFormController)
    .service('CrmAtcsdocumentos', CrmAtcsdocumentos)
    .controller('CrmAtcsdocumentosListController', CrmAtcsdocumentosListController)
    .controller('CrmAtcsdocumentosFormNewController', CrmAtcsdocumentosFormNewController)
    .controller('CrmAtcsdocumentosFormShowController', CrmAtcsdocumentosFormShowController)
    .component('crmAtcsdocumentosDefaultShow', CrmAtcsdocumentosDefaultShow)
    .component('crmAtcsdocumentosDefault', CrmAtcsdocumentosDefault)
    .constant('FIELDS_CrmAtcsdocumentos', FIELDS_CrmAtcsdocumentos)
    .constant('OPTIONS_CrmAtcsdocumentos', OPTIONS_CrmAtcsdocumentos)
    .constant('MAXOCCURS_CrmAtcsdocumentos', MAXOCCURS_CrmAtcsdocumentos)
    .constant('SELECT_Status_CrmAtcsdocumentos', SELECT_Status_CrmAtcsdocumentos)
    .constant('SELECTS_CrmAtcsdocumentos', SELECTS_CrmAtcsdocumentos)
    .run(['$rootScope', 'FIELDS_CrmAtcsdocumentos', 'OPTIONS_CrmAtcsdocumentos', 'MAXOCCURS_CrmAtcsdocumentos', 'SELECTS_CrmAtcsdocumentos', 'SELECT_Status_CrmAtcsdocumentos',
        ($rootScope: any, FIELDS_CrmAtcsdocumentos: object, OPTIONS_CrmAtcsdocumentos: object, MAXOCCURS_CrmAtcsdocumentos: object, SELECTS_CrmAtcsdocumentos: object, SELECT_Status_CrmAtcsdocumentos: object) => {
            $rootScope.OPTIONS_CrmAtcsdocumentos = OPTIONS_CrmAtcsdocumentos;
            $rootScope.MAXOCCURS_CrmAtcsdocumentos = MAXOCCURS_CrmAtcsdocumentos;
            $rootScope.SELECTS_CrmAtcsdocumentos = SELECTS_CrmAtcsdocumentos;
            $rootScope.SELECT_Status_CrmAtcsdocumentos = SELECT_Status_CrmAtcsdocumentos; $rootScope.FIELDS_CrmAtcsdocumentos = FIELDS_CrmAtcsdocumentos;
        }])
    .config(AtcsdocumentosRoutes)
    .name