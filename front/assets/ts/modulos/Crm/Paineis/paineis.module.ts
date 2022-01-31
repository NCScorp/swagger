import angular from "angular";
import { paineisRoutes } from "./config";
import { MarketingModule } from "./Marketing/marketing.module";
import { CrmPainelController } from "./view";

export const paineisModule =  angular
.module('paineisModule',[MarketingModule])
.controller('CrmPainelController', CrmPainelController)
.config(paineisRoutes)
.name
