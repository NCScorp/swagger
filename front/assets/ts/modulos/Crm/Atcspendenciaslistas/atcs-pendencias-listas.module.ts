import angular from "angular";
import { CrmAtcspendenciaslistasListController } from ".";
import { CrmAtcspendenciaslistasDefault, CrmAtcspendenciaslistasDefaultShow } from "./components";
import { AtcspendenciaslistasRoutes } from "./config";
import { FIELDS_CrmAtcspendenciaslistas } from "./constant";
import { CrmAtcspendenciaslistasDefaultController } from "./default.form";
import { CrmAtcspendenciaslistasDefaultShowController } from "./default.form.show";
import { CrmAtcspendenciaslistasFormController } from "./edit";
import { CrmAtcspendenciaslistas } from "./factory";
import { CrmAtcspendenciaslistasFormModalController, CrmAtcspendenciaslistasFormService } from "./modal";
import { CrmAtcspendenciaslistasFormNewController } from "./new";
import { CrmAtcspendenciaslistasFormShowController } from "./show";

export const AtcspendenciaslistasModule = angular
    .module('AtcspendenciaslistasModule', [])
    .controller('CrmAtcspendenciaslistasDefaultShowController', CrmAtcspendenciaslistasDefaultShowController)
    .controller('CrmAtcspendenciaslistasDefaultController', CrmAtcspendenciaslistasDefaultController)
    .controller('CrmAtcspendenciaslistasFormController', CrmAtcspendenciaslistasFormController)
    .service('CrmAtcspendenciaslistas', CrmAtcspendenciaslistas)
    .controller('CrmAtcspendenciaslistasListController', CrmAtcspendenciaslistasListController)
    .controller('CrmAtcspendenciaslistasFormNewController', CrmAtcspendenciaslistasFormNewController)
    .controller('CrmAtcspendenciaslistasFormShowController', CrmAtcspendenciaslistasFormShowController)
    .controller('CrmAtcspendenciaslistasFormModalController', CrmAtcspendenciaslistasFormModalController)
    .service('CrmAtcspendenciaslistasFormService', CrmAtcspendenciaslistasFormService)
    .component('crmAtcspendenciaslistasDefaultShow', CrmAtcspendenciaslistasDefaultShow)
    .component('crmAtcspendenciaslistasDefault', CrmAtcspendenciaslistasDefault)
    .constant('FIELDS_CrmAtcspendenciaslistas', FIELDS_CrmAtcspendenciaslistas)
    .run(['$rootScope', 'FIELDS_CrmAtcspendenciaslistas',
        ($rootScope: any, FIELDS_CrmAtcspendenciaslistas: object) => {
            $rootScope.FIELDS_CrmAtcspendenciaslistas = FIELDS_CrmAtcspendenciaslistas;
        }])
    .config(AtcspendenciaslistasRoutes)
    .name