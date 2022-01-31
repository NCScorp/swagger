import angular from "angular";
import { CrmAtcsconfiguracoestemplatesemailsListController } from ".";
import { CrmAtcsconfiguracoestemplatesemailsDefaultShow, CrmAtcsconfiguracoestemplatesemailsDefault } from "./components";
import { AtcsconfiguracoestemplatesemailsRoutes } from "./config";
import { CrmAtcsconfiguracoestemplatesemailsDefaultController } from "./default.form";
import { CrmAtcsconfiguracoestemplatesemailsDefaultShowController } from "./default.form.show";
import { CrmAtcsconfiguracoestemplatesemailsFormController } from "./edit";
import { CrmAtcsconfiguracoestemplatesemails } from "./factory";
import { CrmAtcsconfiguracoestemplatesemailsFormNewController } from "./new";
import { CrmAtcsconfiguracoestemplatesemailsFormShowController } from "./show";

export const AtcsconfiguracoestemplatesemailsModule = angular
    .module('AtcsconfiguracoestemplatesemailsModule', [])
    .component('crmAtcsconfiguracoestemplatesemailsDefaultShow', CrmAtcsconfiguracoestemplatesemailsDefaultShow)
    .component('crmAtcsconfiguracoestemplatesemailsDefault', CrmAtcsconfiguracoestemplatesemailsDefault)
    .controller('CrmAtcsconfiguracoestemplatesemailsDefaultShowController', CrmAtcsconfiguracoestemplatesemailsDefaultShowController)
    .controller('CrmAtcsconfiguracoestemplatesemailsDefaultController', CrmAtcsconfiguracoestemplatesemailsDefaultController)
    .controller('CrmAtcsconfiguracoestemplatesemailsFormController', CrmAtcsconfiguracoestemplatesemailsFormController)
    .service('CrmAtcsconfiguracoestemplatesemails', CrmAtcsconfiguracoestemplatesemails)
    .controller('CrmAtcsconfiguracoestemplatesemailsListController', CrmAtcsconfiguracoestemplatesemailsListController)
    .controller('CrmAtcsconfiguracoestemplatesemailsFormNewController', CrmAtcsconfiguracoestemplatesemailsFormNewController)
    .controller('CrmAtcsconfiguracoestemplatesemailsFormShowController', CrmAtcsconfiguracoestemplatesemailsFormShowController)
    .constant('FIELDS_CrmAtcsconfiguracoestemplatesemails', [
    ])
    .run(['$rootScope', 'FIELDS_CrmAtcsconfiguracoestemplatesemails',
        ($rootScope: any, FIELDS_CrmAtcsconfiguracoestemplatesemails: object) => {
            $rootScope.FIELDS_CrmAtcsconfiguracoestemplatesemails = FIELDS_CrmAtcsconfiguracoestemplatesemails;
        }])
    .config(AtcsconfiguracoestemplatesemailsRoutes)
    .name