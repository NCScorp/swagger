import angular from "angular";
import { CrmAtcsareaspendenciaslistasDefaultShow, CrmAtcsareaspendenciaslistasDefault } from "./components";
import { CrmAtcsareaspendenciaslistasDefaultController } from "./default.form";
import { CrmAtcsareaspendenciaslistasDefaultShowController } from "./default.form.show";
import { CrmAtcsareaspendenciaslistas } from "./factory";
import { CrmAtcsareaspendenciaslistasFormModalController, CrmAtcsareaspendenciaslistasFormService } from "./modal";
import { CrmAtcsareaspendenciaslistasFormModalShowController, CrmAtcsareaspendenciaslistasFormShowService } from "./modal.show";


export const AtcsAreasPendenciasListasModule = angular.module('AtcsAreasPendenciasListasModule', [])
    .component('crmAtcsareaspendenciaslistasDefaultShow', CrmAtcsareaspendenciaslistasDefaultShow)
    .component('crmAtcsareaspendenciaslistasDefault', CrmAtcsareaspendenciaslistasDefault)
    .controller('CrmAtcsareaspendenciaslistasDefaultShowController', CrmAtcsareaspendenciaslistasDefaultShowController)
    .controller('CrmAtcsareaspendenciaslistasDefaultController', CrmAtcsareaspendenciaslistasDefaultController)
    .service('CrmAtcsareaspendenciaslistas', CrmAtcsareaspendenciaslistas)
    .controller('CrmAtcsareaspendenciaslistasFormModalShowController', CrmAtcsareaspendenciaslistasFormModalShowController)
    .service('CrmAtcsareaspendenciaslistasFormShowService', CrmAtcsareaspendenciaslistasFormShowService)
    .controller('CrmAtcsareaspendenciaslistasFormModalController', CrmAtcsareaspendenciaslistasFormModalController)
    .service('CrmAtcsareaspendenciaslistasFormService', CrmAtcsareaspendenciaslistasFormService)
    .name
