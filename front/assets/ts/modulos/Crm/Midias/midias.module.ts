import angular from "angular";
import { CrmMidiasListController } from ".";
import { crmMidiasDefaultShow, crmMidiasDefault } from "./components";
import { midiasRoutes } from "./config";
import { FIELDS_CrmMidias } from "./constant";
import { CrmMidiasDefaultController } from "./default.form";
import { CrmMidiasDefaultShowController } from "./default.form.show";
import { CrmMidiasFormController } from "./edit";
import { CrmMidias } from "./factory";
import { CrmMidiasFormNewController } from "./new";
import { CrmMidiasFormShowController } from "./show";

export const midiasModule = angular
    .module('midiasModule', [])
    .controller('CrmMidiasDefaultShowController', CrmMidiasDefaultShowController)
    .controller('CrmMidiasDefaultController', CrmMidiasDefaultController)
    .controller('CrmMidiasFormController', CrmMidiasFormController)
    .service('CrmMidias', CrmMidias)
    .controller('CrmMidiasListController', CrmMidiasListController)
    .controller('CrmMidiasFormNewController', CrmMidiasFormNewController)
    .controller('CrmMidiasFormShowController', CrmMidiasFormShowController)
    .component('crmMidiasDefaultShow', crmMidiasDefaultShow)
    .component('crmMidiasDefault', crmMidiasDefault)
    .constant('FIELDS_CrmMidias', FIELDS_CrmMidias)
    .run(['$rootScope', 'FIELDS_CrmMidias',
        ($rootScope: any, FIELDS_CrmMidias: object) => {
            $rootScope.FIELDS_CrmMidias = FIELDS_CrmMidias;
        }])
    .config(midiasRoutes)
    .name