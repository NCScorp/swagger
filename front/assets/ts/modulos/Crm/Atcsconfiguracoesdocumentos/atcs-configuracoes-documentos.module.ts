import angular from "angular";
import { CrmAtcsconfiguracoesdocumentosListController } from ".";
import { AtcsDocumentosTreeService } from "./atcsDocumentosTreeService";
import { CrmAtcsconfiguracoesdocumentosDefaultShow, CrmAtcsconfiguracoesdocumentosDefault } from "./components";
import { atcsConfiguracoesDocumentosRoutes } from "./config";
import { CrmConfiguracaoDocumentosController } from "./configuracao-documentos";
import { CrmAtcsconfiguracoesdocumentosDefaultController } from "./default.form";
import { CrmAtcsconfiguracoesdocumentosDefaultShowController } from "./default.form.show";
import { CrmAtcsconfiguracoesdocumentosFormController } from "./edit";
import { CrmAtcsconfiguracoesdocumentos } from "./factory";
import { CrmAtcsconfiguracoesdocumentosFormNewController } from "./new";
import { CrmAtcsconfiguracoesdocumentosFormShowController } from "./show";

export const atcsConfiguracoesDocumentosModule = angular
    .module('atcsConfiguracoesDocumentosModule', [])
    .component('crmAtcsconfiguracoesdocumentosDefaultShow', CrmAtcsconfiguracoesdocumentosDefaultShow)
    .component('crmAtcsconfiguracoesdocumentosDefault', CrmAtcsconfiguracoesdocumentosDefault)
    .service('AtcsDocumentosTreeService', AtcsDocumentosTreeService)
    .controller('CrmConfiguracaoDocumentosController', CrmConfiguracaoDocumentosController)
    .controller('CrmAtcsconfiguracoesdocumentosDefaultShowController', CrmAtcsconfiguracoesdocumentosDefaultShowController)
    .controller('CrmAtcsconfiguracoesdocumentosDefaultController', CrmAtcsconfiguracoesdocumentosDefaultController)
    .controller('CrmAtcsconfiguracoesdocumentosFormController', CrmAtcsconfiguracoesdocumentosFormController)
    .service('CrmAtcsconfiguracoesdocumentos', CrmAtcsconfiguracoesdocumentos)
    .controller('CrmAtcsconfiguracoesdocumentosListController', CrmAtcsconfiguracoesdocumentosListController)
    .controller('CrmAtcsconfiguracoesdocumentosFormNewController', CrmAtcsconfiguracoesdocumentosFormNewController)
    .controller('CrmAtcsconfiguracoesdocumentosFormShowController', CrmAtcsconfiguracoesdocumentosFormShowController)
    .constant('FIELDS_CrmAtcsconfiguracoesdocumentos', [
    ])
    .run(['$rootScope', 'FIELDS_CrmAtcsconfiguracoesdocumentos',
        ($rootScope: any, FIELDS_CrmAtcsconfiguracoesdocumentos: object) => {
            $rootScope.FIELDS_CrmAtcsconfiguracoesdocumentos = FIELDS_CrmAtcsconfiguracoesdocumentos;
        }])
    .config(atcsConfiguracoesDocumentosRoutes)
    .name