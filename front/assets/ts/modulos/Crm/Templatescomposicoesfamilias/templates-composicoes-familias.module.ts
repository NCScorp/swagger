import angular from "angular";
import { crmTemplatescomposicoesfamiliasDefault } from "./components";
import { CrmTemplatescomposicoesfamiliasDefaultController } from "./default.form";
import { CrmTemplatescomposicoesfamilias } from "./factory";
import { CrmTemplatescomposicoesfamiliasFormModalController, CrmTemplatescomposicoesfamiliasFormService } from "./modal";

export const TemplatescomposicoesfamiliasModule = angular
    .module('TemplatescomposicoesfamiliasModule', [])
    .component('crmTemplatescomposicoesfamiliasDefault', crmTemplatescomposicoesfamiliasDefault)
    .controller('CrmTemplatescomposicoesfamiliasDefaultController', CrmTemplatescomposicoesfamiliasDefaultController)
    .controller('CrmTemplatescomposicoesfamiliasFormModalController', CrmTemplatescomposicoesfamiliasFormModalController)
    .service('CrmTemplatescomposicoesfamiliasFormService', CrmTemplatescomposicoesfamiliasFormService)
    .service('CrmTemplatescomposicoesfamilias', CrmTemplatescomposicoesfamilias)
    .name