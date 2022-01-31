import angular from "angular";
import { CrmHistoricospadraoListController } from ".";
import { crmHistoricospadraoDefaultShow, crmHistoricospadraoDefault } from "./components";
import { HistoricospadraoRoutes } from "./config";
import { FIELDS_CrmHistoricospadrao } from "./constant";
import { CrmHistoricospadraoDefaultController } from "./default.form";
import { CrmHistoricospadraoDefaultShowController } from "./default.form.show";
import { CrmHistoricospadraoFormController } from "./edit";
import { CrmHistoricospadrao } from "./factory";
import { CrmHistoricospadraoFormNewController } from "./new";
import { CrmHistoricospadraoFormShowController } from "./show";
import { ModalExclusaoHistoricoPadraoController, ModalExclusaoHistoricoPadraoService } from "./modal-exclusao";

export const HistoricospadraoModule = angular
    .module('HistoricospadraoModule', [])
    .controller('CrmHistoricospadraoDefaultShowController', CrmHistoricospadraoDefaultShowController)
    .controller('CrmHistoricospadraoDefaultController', CrmHistoricospadraoDefaultController)
    .controller('CrmHistoricospadraoFormController', CrmHistoricospadraoFormController)
    .service('CrmHistoricospadrao', CrmHistoricospadrao)
    .controller('CrmHistoricospadraoListController', CrmHistoricospadraoListController)
    .controller('CrmHistoricospadraoFormNewController', CrmHistoricospadraoFormNewController)
    .controller('CrmHistoricospadraoFormShowController', CrmHistoricospadraoFormShowController)
    .service('modalExclusaoHistoricoPadraoService', ModalExclusaoHistoricoPadraoService)
    .controller('ModalExclusaoHistoricoPadraoController', ModalExclusaoHistoricoPadraoController)
    .component('crmHistoricospadraoDefaultShow', crmHistoricospadraoDefaultShow)
    .component('crmHistoricospadraoDefault', crmHistoricospadraoDefault)
    .constant('FIELDS_CrmHistoricospadrao', FIELDS_CrmHistoricospadrao)
    .constant('OPTIONS_CrmHistoricospadrao', { 'tipo': 'Tipo', })
    .constant('MAXOCCURS_CrmHistoricospadrao', {})
    .constant('SELECTS_CrmHistoricospadrao', { 'tipo': { '100': 'Geral', '101': 'Acompanhamento', '102': 'Pendências', }, })
    .run(['$rootScope', 'FIELDS_CrmHistoricospadrao', 'OPTIONS_CrmHistoricospadrao', 'MAXOCCURS_CrmHistoricospadrao', 'SELECTS_CrmHistoricospadrao',
        ($rootScope: any, FIELDS_CrmHistoricospadrao: object, OPTIONS_CrmHistoricospadrao: object, MAXOCCURS_CrmHistoricospadrao: object, SELECTS_CrmHistoricospadrao: object) => {
            $rootScope.OPTIONS_CrmHistoricospadrao = OPTIONS_CrmHistoricospadrao;
            $rootScope.MAXOCCURS_CrmHistoricospadrao = MAXOCCURS_CrmHistoricospadrao;
            $rootScope.SELECTS_CrmHistoricospadrao = SELECTS_CrmHistoricospadrao;
            $rootScope.FIELDS_CrmHistoricospadrao = FIELDS_CrmHistoricospadrao;
        }])
    .config(HistoricospadraoRoutes)
    .name