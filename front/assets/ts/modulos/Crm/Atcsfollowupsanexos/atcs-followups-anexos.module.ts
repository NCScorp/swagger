import angular from "angular";
import { CrmAtcsfollowupsanexosListController } from ".";
import { CrmAtcsfollowupsanexosDefault } from "./components";
import { AtcsfollowupsanexosRoutes } from "./config";
import { FIELDS_CrmAtcsfollowupsanexos } from "./constant";
import { CrmAtcsfollowupsanexosDefaultController } from "./default.form";
import { CrmAtcsfollowupsanexosFormController } from "./edit";
import { CrmAtcsfollowupsanexos } from "./factory";
import { CrmAtcsfollowupsanexosFormNewController } from "./new";

export const AtcsfollowupsanexosModule = angular
    .module('AtcsfollowupsanexosModule', [])
    .controller('CrmAtcsfollowupsanexosDefaultController', CrmAtcsfollowupsanexosDefaultController)
    .controller('CrmAtcsfollowupsanexosFormController', CrmAtcsfollowupsanexosFormController)
    .service('CrmAtcsfollowupsanexos', CrmAtcsfollowupsanexos)
    .controller('CrmAtcsfollowupsanexosListController', CrmAtcsfollowupsanexosListController)
    .controller('CrmAtcsfollowupsanexosFormNewController', CrmAtcsfollowupsanexosFormNewController)
    .component('crmAtcsfollowupsanexosDefault', CrmAtcsfollowupsanexosDefault)
    .constant('FIELDS_CrmAtcsfollowupsanexos', FIELDS_CrmAtcsfollowupsanexos)
    .run(['$rootScope', 'FIELDS_CrmAtcsfollowupsanexos',
        ($rootScope: any, FIELDS_CrmAtcsfollowupsanexos: object) => {
            $rootScope.FIELDS_CrmAtcsfollowupsanexos = FIELDS_CrmAtcsfollowupsanexos;
        }])
    .config(AtcsfollowupsanexosRoutes)
    .name