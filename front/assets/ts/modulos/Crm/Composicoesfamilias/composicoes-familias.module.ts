import angular from "angular";
import { crmComposicoesfamiliasDefault } from "./components";
import { CrmComposicoesfamiliasDefaultController } from "./default.form";
import { CrmComposicoesfamilias } from "./factory";
import { CrmComposicoesfamiliasFormModalController, CrmComposicoesfamiliasFormService } from "./modal";

export const ComposicoesfamiliasModule = angular
    .module('ComposicoesfamiliasModule', [])
    .component('crmComposicoesfamiliasDefault', crmComposicoesfamiliasDefault)
    .controller('CrmComposicoesfamiliasDefaultController', CrmComposicoesfamiliasDefaultController)
    .service('CrmComposicoesfamilias', CrmComposicoesfamilias)
    .controller('CrmComposicoesfamiliasFormModalController', CrmComposicoesfamiliasFormModalController)
    .service('CrmComposicoesfamiliasFormService', CrmComposicoesfamiliasFormService)
    .name

