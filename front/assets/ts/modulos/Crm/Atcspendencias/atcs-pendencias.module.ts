import angular from "angular"
import { CrmAtcspendenciasDefaultShow, CrmAtcspendenciasDefault, crmAtcspendenciasEdit } from "./components"
import { CrmAtcspendenciasDefaultController } from "./default.form"
import { CrmAtcspendenciasDefaultShowController } from "./default.form.show"
import { CrmAtcspendenciasEditController } from "./edit.form"
import { CrmAtcspendencias } from "./factory"
import { CrmAtcspendenciasFormModalController, CrmAtcspendenciasFormService } from "./modal"
import { CrmAtcspendenciasFormModalShowController, CrmAtcspendenciasFormShowService } from "./modal.show"

export const AtcspendenciasModule = angular
    .module('AtcspendenciasModule', [])
    .component('crmAtcspendenciasDefaultShow', CrmAtcspendenciasDefaultShow)
    .component('crmAtcspendenciasDefault', CrmAtcspendenciasDefault)
    .component('crmAtcspendenciasEdit', crmAtcspendenciasEdit)
    .controller('CrmAtcspendenciasDefaultShowController', CrmAtcspendenciasDefaultShowController)
    .controller('CrmAtcspendenciasDefaultController', CrmAtcspendenciasDefaultController)
    .controller('CrmAtcspendenciasEditController', CrmAtcspendenciasEditController)
    .service('CrmAtcspendencias', CrmAtcspendencias)
    .controller('CrmAtcspendenciasFormModalShowController', CrmAtcspendenciasFormModalShowController)
    .service('CrmAtcspendenciasFormShowService', CrmAtcspendenciasFormShowService)
    .controller('CrmAtcspendenciasFormModalController', CrmAtcspendenciasFormModalController)
    .service('CrmAtcspendenciasFormService', CrmAtcspendenciasFormService)
    .name
