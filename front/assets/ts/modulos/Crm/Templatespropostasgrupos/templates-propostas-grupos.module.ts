import angular from "angular";
import { crmTemplatespropostasgruposDefault } from "./components";
import { CrmTemplatespropostasgruposDefaultController } from "./default.form";
import { CrmTemplatespropostasgrupos } from "./factory";
import { CrmTemplatespropostasgruposFormModalController, CrmTemplatespropostasgruposFormService } from "./modal";

export const TemplatespropostasgruposModule = angular
    .module('TemplatespropostasgruposModule', [])
    .component('crmTemplatespropostasgruposDefault', crmTemplatespropostasgruposDefault)
    .controller('CrmTemplatespropostasgruposDefaultController', CrmTemplatespropostasgruposDefaultController)
    .service('CrmTemplatespropostasgrupos', CrmTemplatespropostasgrupos)
    .controller('CrmTemplatespropostasgruposFormModalController', CrmTemplatespropostasgruposFormModalController)
    .service('CrmTemplatespropostasgruposFormService', CrmTemplatespropostasgruposFormService)
    .name