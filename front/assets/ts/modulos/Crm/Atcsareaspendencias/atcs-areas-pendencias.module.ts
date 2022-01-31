import angular from "angular";
import { CrmAtcsareaspendenciasDefaultShow, CrmAtcsareaspendenciasDefault } from "./components";
import { CrmAtcsareaspendenciasDefaultController } from "./default.form";
import { CrmAtcsareaspendenciasDefaultShowController } from "./default.form.show";
import { CrmAtcsareaspendencias } from "./factory";
import { CrmAtcsareaspendenciasFormModalController, CrmAtcsareaspendenciasFormService } from "./modal";
import { CrmAtcsareaspendenciasFormModalShowController, CrmAtcsareaspendenciasFormShowService } from "./modal.show";

export const atcsAreasPendenciasModule = angular.module('atcsAreasPendenciasModule', [])
    .controller('CrmAtcsareaspendenciasDefaultShowController', CrmAtcsareaspendenciasDefaultShowController)
    .controller('CrmAtcsareaspendenciasDefaultController', CrmAtcsareaspendenciasDefaultController)
    .component('crmAtcsareaspendenciasDefaultShow', CrmAtcsareaspendenciasDefaultShow)
    .component('crmAtcsareaspendenciasDefault', CrmAtcsareaspendenciasDefault)
    .service('CrmAtcsareaspendencias', CrmAtcsareaspendencias)
    .controller('CrmAtcsareaspendenciasFormModalShowController', CrmAtcsareaspendenciasFormModalShowController)
    .service('CrmAtcsareaspendenciasFormShowService', CrmAtcsareaspendenciasFormShowService)
    .controller('CrmAtcsareaspendenciasFormModalController', CrmAtcsareaspendenciasFormModalController)
    .service('CrmAtcsareaspendenciasFormService', CrmAtcsareaspendenciasFormService)
    .name