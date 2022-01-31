import angular from "angular";
import { CrmAtcsresponsaveisfinanceirosDefault } from "./components";
import { CrmAtcsresponsaveisfinanceirosDefaultController } from "./default.form";
import { CrmAtcsresponsaveisfinanceiros } from "./factory";
import { CrmAtcsresponsaveisfinanceirosFormModalController, CrmAtcsresponsaveisfinanceirosFormService } from "./modal";

export const AtcsresponsaveisfinanceirosModule = angular
    .module('AtcsresponsaveisfinanceirosModule', [])
    .component('crmAtcsresponsaveisfinanceirosDefault', CrmAtcsresponsaveisfinanceirosDefault)
    .controller('CrmAtcsresponsaveisfinanceirosDefaultController', CrmAtcsresponsaveisfinanceirosDefaultController)
    .service('CrmAtcsresponsaveisfinanceiros', CrmAtcsresponsaveisfinanceiros)
    .controller('CrmAtcsresponsaveisfinanceirosFormModalController', CrmAtcsresponsaveisfinanceirosFormModalController)
    .service('CrmAtcsresponsaveisfinanceirosFormService', CrmAtcsresponsaveisfinanceirosFormService)
    .name