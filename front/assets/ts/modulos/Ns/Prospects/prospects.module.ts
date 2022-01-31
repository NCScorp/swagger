import angular from "angular";
import { NsProspectsListController } from ".";
import { nsProspectsDefault } from "./components";
import { ProspectsRoutes } from "./config";
import { FIELDS_NsProspects } from "./constant";
import { NsProspectsDefaultController } from "./default.form";
import { NsProspectsFormController } from "./edit";
import { NsProspects } from "./factory";
import { NsProspectsFormNewController } from "./new";

export const ProspectsModule = angular
    .module('ProspectsModule', [])
    .controller('NsProspectsDefaultController', NsProspectsDefaultController)
    .controller('NsProspectsFormController', NsProspectsFormController)
    .service('NsProspects', NsProspects)
    .controller('NsProspectsListController', NsProspectsListController)
    .controller('NsProspectsFormNewController', NsProspectsFormNewController)
    .component('nsProspectsDefault', nsProspectsDefault)
    .constant('FIELDS_NsProspects',FIELDS_NsProspects)
    .run(['$rootScope', 'FIELDS_NsProspects',
        ($rootScope: any, FIELDS_NsProspects: object) => {
            $rootScope.FIELDS_NsProspects = FIELDS_NsProspects;
        }])
    .config(ProspectsRoutes)
    .name