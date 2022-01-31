import angular from "angular";
import { CrmOrcamentosListController } from ".";
import { crmOrcamentosDefaultShow, crmOrcamentosDefault } from "./components";
import { OrcamentoRoutes } from "./config";
import { FIELDS_CrmOrcamentos } from "./constant";
import { CrmOrcamentosDefaultController } from "./default.form";
import { CrmOrcamentosDefaultShowController } from "./default.form.show";
import { CrmOrcamentosFormController } from "./edit";
import { CrmOrcamentos } from "./factory";
import { CrmOrcamentosFormNewController } from "./new";
import { CrmOrcamentosFormShowController } from "./show";

export const OrcamentosModule = angular
    .module('OrcamentosModule', [])
    .service('CrmOrcamentos', CrmOrcamentos)
    .controller('CrmOrcamentosDefaultShowController', CrmOrcamentosDefaultShowController)
    .controller('CrmOrcamentosDefaultController', CrmOrcamentosDefaultController)
    .controller('CrmOrcamentosFormController', CrmOrcamentosFormController)
    .controller('CrmOrcamentosListController', CrmOrcamentosListController)
    .controller('CrmOrcamentosFormNewController', CrmOrcamentosFormNewController)
    .controller('CrmOrcamentosFormShowController', CrmOrcamentosFormShowController)
    .component('crmOrcamentosDefaultShow', crmOrcamentosDefaultShow)
    .component('crmOrcamentosDefault', crmOrcamentosDefault)
    .constant('FIELDS_CrmOrcamentos', FIELDS_CrmOrcamentos)
    .constant('OPTIONS_CrmOrcamentos', { 'propostaitem': 'propostaitem', 'propostaitemfamilia': 'propostaitemfamilia', 'propostaitemfuncao': 'propostaitemfuncao', 'execucaodeservico': 'execucaodeservico', 'itemfaturamento': 'itemfaturamento', 'fornecedor': 'fornecedor', 'propostaitem.negocio': 'propostaitem.negocio', })
    .constant('MAXOCCURS_CrmOrcamentos', {})
    .constant('SELECTS_CrmOrcamentos', {})
    .run(['$rootScope', 'FIELDS_CrmOrcamentos', 'OPTIONS_CrmOrcamentos', 'MAXOCCURS_CrmOrcamentos', 'SELECTS_CrmOrcamentos',
        ($rootScope: any, FIELDS_CrmOrcamentos: object, OPTIONS_CrmOrcamentos: object, MAXOCCURS_CrmOrcamentos: object, SELECTS_CrmOrcamentos: object) => {
            $rootScope.OPTIONS_CrmOrcamentos = OPTIONS_CrmOrcamentos;
            $rootScope.MAXOCCURS_CrmOrcamentos = MAXOCCURS_CrmOrcamentos;
            $rootScope.SELECTS_CrmOrcamentos = SELECTS_CrmOrcamentos;
            $rootScope.FIELDS_CrmOrcamentos = FIELDS_CrmOrcamentos;
        }])
    .config(OrcamentoRoutes)
    .name