import angular from "angular";
import { CrmAtcsconfiguracoesdocumentositensListController } from ".";
import { CrmAtcsconfiguracoesdocumentositensDefaultShow, CrmAtcsconfiguracoesdocumentositensDefault } from "./components";
import { atcsConfiguracoesDocumentosRoutes } from "./config";
import { CrmAtcsconfiguracoesdocumentositensDefaultController } from "./default.form";
import { CrmAtcsconfiguracoesdocumentositensDefaultShowController } from "./default.form.show";
import { CrmAtcsconfiguracoesdocumentositensFormController } from "./edit";
import { CrmAtcsconfiguracoesdocumentositens } from "./factory";
import { CrmAtcsconfiguracoesdocumentositensFormNewController } from "./new";
import { CrmAtcsconfiguracoesdocumentositensFormShowController } from "./show";

export const AtcsconfiguracoesdocumentositensModule = angular
    .module('AtcsconfiguracoesdocumentositensModule', [])
    .controller('CrmAtcsconfiguracoesdocumentositensDefaultShowController', CrmAtcsconfiguracoesdocumentositensDefaultShowController)
    .controller('CrmAtcsconfiguracoesdocumentositensDefaultController', CrmAtcsconfiguracoesdocumentositensDefaultController)
    .controller('CrmAtcsconfiguracoesdocumentositensFormController', CrmAtcsconfiguracoesdocumentositensFormController)
    .service('CrmAtcsconfiguracoesdocumentositens', CrmAtcsconfiguracoesdocumentositens)
    .controller('CrmAtcsconfiguracoesdocumentositensListController', CrmAtcsconfiguracoesdocumentositensListController)
    .controller('CrmAtcsconfiguracoesdocumentositensFormNewController', CrmAtcsconfiguracoesdocumentositensFormNewController)
    .controller('CrmAtcsconfiguracoesdocumentositensFormShowController', CrmAtcsconfiguracoesdocumentositensFormShowController)
    .component('crmAtcsconfiguracoesdocumentositensDefaultShow', CrmAtcsconfiguracoesdocumentositensDefaultShow)
    .component('crmAtcsconfiguracoesdocumentositensDefault', CrmAtcsconfiguracoesdocumentositensDefault)
    .constant('FIELDS_CrmAtcsconfiguracoesdocumentositens', [
    ])
    .run(['$rootScope', 'FIELDS_CrmAtcsconfiguracoesdocumentositens',
        ($rootScope: any, FIELDS_CrmAtcsconfiguracoesdocumentositens: object) => {
            $rootScope.FIELDS_CrmAtcsconfiguracoesdocumentositens = FIELDS_CrmAtcsconfiguracoesdocumentositens;
        }])
    .config(atcsConfiguracoesDocumentosRoutes)
    .name