import angular from "angular";
import { CrmAtcsfollowupsListController } from ".";
import { CrmAtcsfollowupsDefaultShow, CrmAtcsfollowupsDefault } from "./components";
import { AtcsfollowupsRoutes } from "./config";
import { FIELDS_CrmAtcsfollowups } from "./constant";
import { CrmAtcsfollowupsDefaultController } from "./default.form";
import { CrmAtcsfollowupsDefaultShowController } from "./default.form.show";
import { CrmAtcsfollowupsFormController } from "./edit";
import { CrmAtcsfollowups } from "./factory";
import { CrmModalAnexoService, CrmModalAnexoController } from "./modal-anexo";
import { CrmAtcsfollowupsFormNewController } from "./new";
import { CrmAtcsfollowupsFormShowController } from "./show";

export const AtcsfollowupsModule = angular
    .module('AtcsfollowupsModule', [])
    .controller('CrmAtcsfollowupsDefaultShowController', CrmAtcsfollowupsDefaultShowController)
    .controller('CrmAtcsfollowupsFormController', CrmAtcsfollowupsFormController)
    .service('CrmAtcsfollowups', CrmAtcsfollowups)
    .controller('CrmAtcsfollowupsListController', CrmAtcsfollowupsListController)
    .service('CrmModalAnexoService', CrmModalAnexoService)
    .controller('CrmAtcsfollowupsFormNewController', CrmAtcsfollowupsFormNewController)
    .controller('CrmAtcsfollowupsFormShowController', CrmAtcsfollowupsFormShowController)
    .controller('CrmModalAnexoController', CrmModalAnexoController)
    .controller('CrmAtcsfollowupsDefaultController', CrmAtcsfollowupsDefaultController)
    .component('crmAtcsfollowupsDefaultShow', CrmAtcsfollowupsDefaultShow)
    .component('crmAtcsfollowupsDefault', CrmAtcsfollowupsDefault)
    .constant('FIELDS_CrmAtcsfollowups', FIELDS_CrmAtcsfollowups)
    .run(['$rootScope', 'FIELDS_CrmAtcsfollowups',
        ($rootScope: any, FIELDS_CrmAtcsfollowups: object) => {
            $rootScope.FIELDS_CrmAtcsfollowups = FIELDS_CrmAtcsfollowups;
        }])
    .config(AtcsfollowupsRoutes)
    .name