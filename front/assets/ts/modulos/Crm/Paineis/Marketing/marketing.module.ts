import angular from "angular";
import { crmPainelMarketing } from "./components";
import { CrmPainelMarketing } from "./factory";
import { CrmPainelMarketingController } from "./view";

export const MarketingModule =  angular
.module('MarketingModule',[])
.controller('CrmPainelMarketingController', CrmPainelMarketingController)
.component('crmPainelMarketing',crmPainelMarketing)
.service('CrmPainelMarketing', CrmPainelMarketing)
.name

