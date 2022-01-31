import angular from "angular";
import { crmTemplatespropostascapitulosDefault } from "./components";
import { CrmTemplatespropostascapitulosDefaultController } from "./default.form";
import { CrmTemplatespropostascapitulos } from "./factory";
import { CrmTemplatespropostascapitulosFormModalController, CrmTemplatespropostascapitulosFormService } from "./modal";

export const TemplatespropostascapitulosModule = angular
    .module('TemplatespropostascapitulosModule', [])
    .component('crmTemplatespropostascapitulosDefault', crmTemplatespropostascapitulosDefault)
    .controller('CrmTemplatespropostascapitulosDefaultController', CrmTemplatespropostascapitulosDefaultController)
    .service('CrmTemplatespropostascapitulos', CrmTemplatespropostascapitulos)
    .controller('CrmTemplatespropostascapitulosFormModalController', CrmTemplatespropostascapitulosFormModalController)
    .service('CrmTemplatespropostascapitulosFormService', CrmTemplatespropostascapitulosFormService)
    .name