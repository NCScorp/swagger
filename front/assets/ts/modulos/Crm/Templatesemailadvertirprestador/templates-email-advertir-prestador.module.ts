import angular from "angular";
import { CrmTemplatesemailadvertirprestadorListController } from ".";
import { crmTemplatesemailadvertirprestadorDefaultShow, crmTemplatesemailadvertirprestadorDefault } from "./components";
import { TemplatesemailadvertirprestadorRoutes } from "./config";
import { FIELDS_CrmTemplatesemailadvertirprestador } from "./constant";
import { CrmTemplatesemailadvertirprestadorDefaultController } from "./default.form";
import { CrmTemplatesemailadvertirprestadorDefaultShowController } from "./default.form.show";
import { CrmTemplatesemailadvertirprestadorFormController } from "./edit";
import { CrmTemplatesemailadvertirprestador } from "./factory";
import { CrmTemplatesemailadvertirprestadorFormNewController } from "./new";
import { CrmTemplatesemailadvertirprestadorFormShowController } from "./show";

export const TemplatesemailadvertirprestadorModule = angular
    .module('TemplatesemailadvertirprestadorModule', [])
    .controller('CrmTemplatesemailadvertirprestadorDefaultShowController', CrmTemplatesemailadvertirprestadorDefaultShowController)
    .controller('CrmTemplatesemailadvertirprestadorDefaultController', CrmTemplatesemailadvertirprestadorDefaultController)
    .controller('CrmTemplatesemailadvertirprestadorFormController', CrmTemplatesemailadvertirprestadorFormController)
    .service('CrmTemplatesemailadvertirprestador', CrmTemplatesemailadvertirprestador)
    .controller('CrmTemplatesemailadvertirprestadorListController', CrmTemplatesemailadvertirprestadorListController)
    .controller('CrmTemplatesemailadvertirprestadorFormNewController', CrmTemplatesemailadvertirprestadorFormNewController)
    .controller('CrmTemplatesemailadvertirprestadorFormShowController', CrmTemplatesemailadvertirprestadorFormShowController)
    .component('crmTemplatesemailadvertirprestadorDefaultShow', crmTemplatesemailadvertirprestadorDefaultShow)
    .component('crmTemplatesemailadvertirprestadorDefault', crmTemplatesemailadvertirprestadorDefault)
    .constant('FIELDS_CrmTemplatesemailadvertirprestador', FIELDS_CrmTemplatesemailadvertirprestador)
    .constant('OPTIONS_CrmTemplatesemailadvertirprestador', { 'estabelecimento': 'Estabelecimento', })
    .constant('MAXOCCURS_CrmTemplatesemailadvertirprestador', {})
    .constant('SELECTS_CrmTemplatesemailadvertirprestador', {})
    .run(['$rootScope', 'FIELDS_CrmTemplatesemailadvertirprestador', 'OPTIONS_CrmTemplatesemailadvertirprestador', 'MAXOCCURS_CrmTemplatesemailadvertirprestador', 'SELECTS_CrmTemplatesemailadvertirprestador',
        ($rootScope: any, FIELDS_CrmTemplatesemailadvertirprestador: object, OPTIONS_CrmTemplatesemailadvertirprestador: object, MAXOCCURS_CrmTemplatesemailadvertirprestador: object, SELECTS_CrmTemplatesemailadvertirprestador: object) => {
            $rootScope.OPTIONS_CrmTemplatesemailadvertirprestador = OPTIONS_CrmTemplatesemailadvertirprestador;
            $rootScope.MAXOCCURS_CrmTemplatesemailadvertirprestador = MAXOCCURS_CrmTemplatesemailadvertirprestador;
            $rootScope.SELECTS_CrmTemplatesemailadvertirprestador = SELECTS_CrmTemplatesemailadvertirprestador;
            $rootScope.FIELDS_CrmTemplatesemailadvertirprestador = FIELDS_CrmTemplatesemailadvertirprestador;
        }])
    .config(TemplatesemailadvertirprestadorRoutes)
    .name