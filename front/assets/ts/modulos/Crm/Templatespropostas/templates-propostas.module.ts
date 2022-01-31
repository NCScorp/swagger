import angular from "angular";
import { crmTemplatespropostasDefault } from "./components";
import { CrmTemplatespropostasDefaultController } from "./default.form";
import { CrmTemplatespropostas } from "./factory";
import { CrmTemplatespropostasFormModalController, CrmTemplatespropostasFormService } from "./modal";
import { CrmTemplatespropostasCreatePropostaFormModalController, CrmTemplatespropostasCreatePropostaFormService } from "./templateproposta-modal";

export const TemplatespropostasModule = angular
    .module('TemplatespropostasModule', [])
    .component('crmTemplatespropostasDefault', crmTemplatespropostasDefault)
    .controller('CrmTemplatespropostasDefaultController', CrmTemplatespropostasDefaultController)
    .service('CrmTemplatespropostas', CrmTemplatespropostas)
    .controller('CrmTemplatespropostasFormModalController', CrmTemplatespropostasFormModalController)
    .service('CrmTemplatespropostasFormService', CrmTemplatespropostasFormService)
    .controller('CrmTemplatespropostasCreatePropostaFormModalController', CrmTemplatespropostasCreatePropostaFormModalController)
    .service('CrmTemplatespropostasCreatePropostaFormService', CrmTemplatespropostasCreatePropostaFormService)
    .name