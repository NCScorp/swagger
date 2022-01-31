import angular from "angular";
import { CrmNegociosoperacoesListController } from ".";
import { crmNegociosoperacoesDefaultShow } from "./components";
import { NegociosoperacoesRoutes } from "./config";
import { FIELDS_CrmNegociosoperacoes } from "./constant";
import { CrmNegociosoperacoesDefaultShowController } from "./default.form.show";
import { CrmNegociosoperacoes } from "./factory";
import { CrmNegociosoperacoesFormShowController } from "./show";

export const NegociosoperacoesModule =  angular
.module('NegociosoperacoesModule',[])
.controller('CrmNegociosoperacoesDefaultShowController', CrmNegociosoperacoesDefaultShowController)
.service('CrmNegociosoperacoes', CrmNegociosoperacoes)
.controller('CrmNegociosoperacoesListController', CrmNegociosoperacoesListController)
.controller('CrmNegociosoperacoesFormShowController', CrmNegociosoperacoesFormShowController)
.component('crmNegociosoperacoesDefaultShow',crmNegociosoperacoesDefaultShow)
.run(['$rootScope', 'FIELDS_CrmNegociosoperacoes',
($rootScope: any, FIELDS_CrmNegociosoperacoes: object) => {
        $rootScope.FIELDS_CrmNegociosoperacoes = FIELDS_CrmNegociosoperacoes;
}])
.constant('FIELDS_CrmNegociosoperacoes',FIELDS_CrmNegociosoperacoes)
.config(NegociosoperacoesRoutes)
.name