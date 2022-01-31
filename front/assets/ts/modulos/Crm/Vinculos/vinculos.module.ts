import angular from "angular";
import { CrmVinculosListController } from ".";
import { crmVinculosDefaultShow, crmVinculosDefault } from "./components";
import { VinculosRoutes } from "./config";
import { FIELDS_CrmVinculos } from "./constant";
import { CrmVinculosDefaultController } from "./default.form";
import { CrmVinculosDefaultShowController } from "./default.form.show";
import { CrmVinculosFormController } from "./edit";
import { CrmVinculos } from "./factory";
import { CrmVinculosFormNewController } from "./new";
import { CrmVinculosFormShowController } from "./show";

export const VinculosModule = angular
    .module('VinculosModule', [])
    .controller('CrmVinculosDefaultShowController', CrmVinculosDefaultShowController)
    .controller('CrmVinculosDefaultController', CrmVinculosDefaultController)
    .controller('CrmVinculosFormController', CrmVinculosFormController)
    .service('CrmVinculos', CrmVinculos)
    .controller('CrmVinculosListController', CrmVinculosListController)
    .controller('CrmVinculosFormNewController', CrmVinculosFormNewController)
    .controller('CrmVinculosFormShowController', CrmVinculosFormShowController)
    .component('crmVinculosDefaultShow', crmVinculosDefaultShow)
    .component('crmVinculosDefault', crmVinculosDefault)
    .constant('FIELDS_CrmVinculos', FIELDS_CrmVinculos)
    .run(['$rootScope', 'FIELDS_CrmVinculos',
        ($rootScope: any, FIELDS_CrmVinculos: object) => {
            $rootScope.FIELDS_CrmVinculos = FIELDS_CrmVinculos;
        }])
    .config(VinculosRoutes)
    .name
