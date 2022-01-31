import angular from "angular";
import { CrmPropostascapitulosListController } from ".";
import { crmPropostascapitulosDefault } from "./components";
import { PropostascapitulosRoutes } from "./config";
import { FIELDS_CrmPropostascapitulos } from "./constant";
import { CrmPropostascapitulosDefaultController } from "./default.form";
import { CrmPropostascapitulosFormController } from "./edit";
import { CrmPropostascapitulos } from "./factory";
import { CrmPropostascapitulosFormModalController, CrmPropostascapitulosFormService } from "./modal";
import { CrmPropostascapitulosFormNewController } from "./new";
import { ModalExclusaoPropCapituloService, ModalExclusaoPropCapituloController } from "./modal-exclusao";

export const PropostascapitulosModule = angular
    .module('PropostascapitulosModule', [])
    .service('modalExclusaoPropCapituloService', ModalExclusaoPropCapituloService)
    .controller('ModalExclusaoPropCapituloController', ModalExclusaoPropCapituloController)
    .controller('CrmPropostascapitulosDefaultController', CrmPropostascapitulosDefaultController)
    .controller('CrmPropostascapitulosFormController', CrmPropostascapitulosFormController)
    .service('CrmPropostascapitulos', CrmPropostascapitulos)
    .controller('CrmPropostascapitulosListController', CrmPropostascapitulosListController)
    .controller('CrmPropostascapitulosFormNewController', CrmPropostascapitulosFormNewController)
    .controller('CrmPropostascapitulosFormModalController', CrmPropostascapitulosFormModalController)
    .service('CrmPropostascapitulosFormService', CrmPropostascapitulosFormService)
    .constant('FIELDS_CrmPropostascapitulos', FIELDS_CrmPropostascapitulos)
    .component('crmPropostascapitulosDefault', crmPropostascapitulosDefault)
    .run(['$rootScope', 'FIELDS_CrmPropostascapitulos',
        ($rootScope: any, FIELDS_CrmPropostascapitulos: object) => {
            $rootScope.FIELDS_CrmPropostascapitulos = FIELDS_CrmPropostascapitulos;
        }])
    .config(PropostascapitulosRoutes)
    .name