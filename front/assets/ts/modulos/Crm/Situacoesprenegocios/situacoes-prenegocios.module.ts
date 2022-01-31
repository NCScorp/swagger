import angular from "angular";
import { CrmSituacoesprenegociosListController } from ".";
import { crmSituacoesprenegociosDefaultShow, crmSituacoesprenegociosDefault } from "./components";
import { SituacoesprenegociosRoutes } from "./config";
import { FIELDS_CrmSituacoesprenegocios } from "./constant";
import { CrmSituacoesprenegociosDefaultController } from "./default.form";
import { CrmSituacoesprenegociosDefaultShowController } from "./default.form.show";
import { CrmSituacoesprenegociosFormController } from "./edit";
import { CrmSituacoesprenegocios } from "./factory";
import { CrmSituacoesprenegociosFormNewController } from "./new";
import { CrmSituacoesprenegociosFormShowController } from "./show";
import { ModalExclusaoSituacaoNegocioController, ModalExclusaoSituacaoNegocioService } from "./modal-exclusao";

export const SituacoesprenegociosModule = angular
    .module('SituacoesprenegociosModule', [])
    .controller('CrmSituacoesprenegociosDefaultShowController', CrmSituacoesprenegociosDefaultShowController)
    .controller('CrmSituacoesprenegociosDefaultController', CrmSituacoesprenegociosDefaultController)
    .controller('CrmSituacoesprenegociosFormController', CrmSituacoesprenegociosFormController)
    .service('CrmSituacoesprenegocios', CrmSituacoesprenegocios)
    .controller('CrmSituacoesprenegociosListController', CrmSituacoesprenegociosListController)
    .controller('CrmSituacoesprenegociosFormNewController', CrmSituacoesprenegociosFormNewController)
    .controller('CrmSituacoesprenegociosFormShowController', CrmSituacoesprenegociosFormShowController)
    .service('modalExclusaoSituacaoNegocioService', ModalExclusaoSituacaoNegocioService)
    .controller('ModalExclusaoSituacaoNegocioController', ModalExclusaoSituacaoNegocioController)
    .component('crmSituacoesprenegociosDefaultShow', crmSituacoesprenegociosDefaultShow)
    .component('crmSituacoesprenegociosDefault', crmSituacoesprenegociosDefault)
    .constant('FIELDS_CrmSituacoesprenegocios', FIELDS_CrmSituacoesprenegocios)
    .run(['$rootScope', 'FIELDS_CrmSituacoesprenegocios',
        ($rootScope: any, FIELDS_CrmSituacoesprenegocios: object) => {
            $rootScope.FIELDS_CrmSituacoesprenegocios = FIELDS_CrmSituacoesprenegocios;
        }])
    .config(SituacoesprenegociosRoutes)
    .name