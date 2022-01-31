import angular from "angular";
import { CrmMalotesdocumentosListController } from ".";
import { crmMalotesdocumentosDefaultShow, crmMalotesdocumentosDefault } from "./components";
import { malotesDocumentosRoutes } from "./config";
import { FIELDS_CrmMalotesdocumentos } from "./constant";
import { CrmMalotesdocumentosDefaultController } from "./default.form";
import { CrmMalotesdocumentosDefaultShowController } from "./default.form.show";
import { CrmMalotesdocumentosFormController } from "./edit";
import { CrmMalotesdocumentos } from "./factory";
import { CrmMalotesdocumentosFormNewController } from "./new";
import { CrmMalotesdocumentosFormShowController } from "./show";

export const malotesDocumentosModule = angular
    .module('malotesDocumentosModule', [])
    .controller('CrmMalotesdocumentosDefaultShowController', CrmMalotesdocumentosDefaultShowController)
    .controller('CrmMalotesdocumentosDefaultController', CrmMalotesdocumentosDefaultController)
    .controller('CrmMalotesdocumentosFormController', CrmMalotesdocumentosFormController)
    .service('CrmMalotesdocumentos', CrmMalotesdocumentos)
    .controller('CrmMalotesdocumentosListController', CrmMalotesdocumentosListController)
    .controller('CrmMalotesdocumentosFormNewController', CrmMalotesdocumentosFormNewController)
    .controller('CrmMalotesdocumentosFormShowController', CrmMalotesdocumentosFormShowController)
    .component('crmMalotesdocumentosDefaultShow', crmMalotesdocumentosDefaultShow)
    .component('crmMalotesdocumentosDefault', crmMalotesdocumentosDefault)
    .constant('FIELDS_CrmMalotesdocumentos', FIELDS_CrmMalotesdocumentos)
    .run(['$rootScope', 'FIELDS_CrmMalotesdocumentos',
        ($rootScope: any, FIELDS_CrmMalotesdocumentos: object) => {
            $rootScope.FIELDS_CrmMalotesdocumentos = FIELDS_CrmMalotesdocumentos;
        }])
    .config(malotesDocumentosRoutes)
    .name
