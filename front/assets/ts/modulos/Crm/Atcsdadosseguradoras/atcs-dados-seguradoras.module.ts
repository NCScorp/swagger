import angular from "angular";
import { CrmAtcsdadosseguradorasListController } from ".";
import { CrmAtcsdadosseguradorasDefault } from "./components";
import { AtcsdadosseguradorasRoutes } from "./config";
import { FIELDS_CrmAtcsdadosseguradoras } from "./constant";
import { CrmAtcsdadosseguradorasDefaultController } from "./default.form";
import { CrmAtcsdadosseguradorasFormController } from "./edit";
import { CrmAtcsdadosseguradoras } from "./factory";
import { CrmAtcsDadosSeguradorasFormModalController, CrmAtcsDadosSeguradorasFormService } from "./modal";
import { CrmAtcsdadosseguradorasFormNewController } from "./new";

export const AtcsdadosseguradorasModule = angular
    .module('AtcsdadosseguradorasModule', [])
    .controller('CrmAtcsdadosseguradorasDefaultController', CrmAtcsdadosseguradorasDefaultController)
    .controller('CrmAtcsdadosseguradorasFormController', CrmAtcsdadosseguradorasFormController)
    .service('CrmAtcsdadosseguradoras', CrmAtcsdadosseguradoras)
    .controller('CrmAtcsdadosseguradorasListController', CrmAtcsdadosseguradorasListController)
    .controller('CrmAtcsdadosseguradorasFormNewController', CrmAtcsdadosseguradorasFormNewController)
    .controller('CrmAtcsDadosSeguradorasFormModalController', CrmAtcsDadosSeguradorasFormModalController)
    .service('CrmAtcsDadosSeguradorasFormService', CrmAtcsDadosSeguradorasFormService)
    .component('crmAtcsdadosseguradorasDefault', CrmAtcsdadosseguradorasDefault)
    .constant('FIELDS_CrmAtcsdadosseguradoras', FIELDS_CrmAtcsdadosseguradoras)
    .run(['$rootScope', 'FIELDS_CrmAtcsdadosseguradoras',
        ($rootScope: any, FIELDS_CrmAtcsdadosseguradoras: object) => {
            $rootScope.FIELDS_CrmAtcsdadosseguradoras = FIELDS_CrmAtcsdadosseguradoras;
        }])
    .config(AtcsdadosseguradorasRoutes)
    .name


