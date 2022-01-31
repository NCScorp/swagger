import angular from "angular";
import { crmPropostasDefaultShow, crmPropostasDefault } from "./components";
import { CrmPropostasDefaultController } from "./default.form";
import { CrmPropostasDefaultShowController } from "./default.form.show";
import { CrmPropostas } from "./factory";
import { CrmPropostasFormModalController, CrmPropostasFormService } from "./modal";
import { CrmPropostasFormModalShowController, CrmPropostasFormShowService } from "./modal.show";

export const PropostasModule = angular
.module('PropostasModule',[])
.controller('CrmPropostasDefaultShowController', CrmPropostasDefaultShowController)
.controller('CrmPropostasDefaultController', CrmPropostasDefaultController)
.service('CrmPropostas', CrmPropostas)
.controller('CrmPropostasFormModalShowController', CrmPropostasFormModalShowController)
.controller('CrmPropostasFormModalController', CrmPropostasFormModalController)
.service('CrmPropostasFormService', CrmPropostasFormService)
.service('CrmPropostasFormShowService', CrmPropostasFormShowService)
.component('crmPropostasDefaultShow',crmPropostasDefaultShow)
.component('crmPropostasDefault',crmPropostasDefault)
.name