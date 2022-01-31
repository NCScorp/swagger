import angular from "angular";
import { CrmPromocoesleadsListController } from ".";
import { crmPromocoesleadsDefaultShow, crmPromocoesleadsDefault } from "./components";
import { PromocoesleadsRoutes } from "./config";
import { FIELDS_CrmPromocoesleads } from "./constant";
import { CrmPromocoesleadsDefaultController } from "./default.form";
import { CrmPromocoesleadsDefaultShowController } from "./default.form.show";
import { CrmPromocoesleadsFormController } from "./edit";
import { CrmPromocoesleads } from "./factory";
import { CrmPromocoesleadsFormNewController } from "./new";
import { CrmPromocoesleadsFormShowController } from "./show";
import { ModalExclusaoCampOrigemController, ModalExclusaoCampOrigemService } from "./modal-exclusao";

export const PromocoesleadsModule = angular
    .module('PromocoesleadsModule', [])
    .controller('CrmPromocoesleadsListController', CrmPromocoesleadsListController)
    .component('crmPromocoesleadsDefaultShow', crmPromocoesleadsDefaultShow)
    .controller('CrmPromocoesleadsFormController', CrmPromocoesleadsFormController)
    .service('CrmPromocoesleads', CrmPromocoesleads)
    .controller('CrmPromocoesleadsFormNewController', CrmPromocoesleadsFormNewController)
    .controller('CrmPromocoesleadsFormShowController', CrmPromocoesleadsFormShowController)
    .component('crmPromocoesleadsDefault', crmPromocoesleadsDefault)
    .controller('CrmPromocoesleadsDefaultShowController', CrmPromocoesleadsDefaultShowController)
    .controller('CrmPromocoesleadsDefaultController', CrmPromocoesleadsDefaultController)
    .service('modalExclusaoCampOrigemService', ModalExclusaoCampOrigemService)
    .controller('ModalExclusaoCampOrigemController', ModalExclusaoCampOrigemController)
    .constant('FIELDS_CrmPromocoesleads', FIELDS_CrmPromocoesleads)
    .constant('OPTIONS_CrmPromocoesleads', { 'bloqueado': 'bloqueado', })
    .constant('MAXOCCURS_CrmPromocoesleads', {})
    .constant('SELECTS_CrmPromocoesleads', {})
    .run(['$rootScope', 'FIELDS_CrmPromocoesleads', 'OPTIONS_CrmPromocoesleads', 'MAXOCCURS_CrmPromocoesleads', 'SELECTS_CrmPromocoesleads',
        ($rootScope: any, FIELDS_CrmPromocoesleads: object, OPTIONS_CrmPromocoesleads: object, MAXOCCURS_CrmPromocoesleads: object, SELECTS_CrmPromocoesleads: object) => {
            $rootScope.OPTIONS_CrmPromocoesleads = OPTIONS_CrmPromocoesleads;
            $rootScope.MAXOCCURS_CrmPromocoesleads = MAXOCCURS_CrmPromocoesleads;
            $rootScope.SELECTS_CrmPromocoesleads = SELECTS_CrmPromocoesleads;
            $rootScope.FIELDS_CrmPromocoesleads = FIELDS_CrmPromocoesleads;
        }])
    .config(PromocoesleadsRoutes)
    .name