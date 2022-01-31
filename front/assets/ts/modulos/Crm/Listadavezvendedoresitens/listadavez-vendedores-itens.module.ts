import angular from "angular";
import { CrmListadavezvendedoresitensListController } from ".";
import { crmListadavezvendedoresitensDefault, crmListadavezvendedoresitensShowShow } from "./components";
import { ListadavezvendedoresitensRoutes } from "./config";
import { CrmListadavezvendedoresitensDefaultController } from "./default.form";
import { CrmListadavezvendedoresitensFormDefaultController, CrmListadavezvendedoresitensFormDefaultService } from "./delete.modal";
import { CrmListadavezvendedoresitensFormController } from "./edit";
import { CrmListadavezvendedoresitens } from "./factory";
import { CrmListadavezvendedoresitensFormNewController } from "./new";
import { CrmListadavezvendedoresitensFormShowController } from "./show";
import { CrmListadavezvendedoresitensShowShowController } from "./show.form.show";

export const ListadavezvendedoresitensModule = angular
    .module('ListadavezvendedoresitensModule', [])
    .controller('CrmListadavezvendedoresitensShowShowController', CrmListadavezvendedoresitensShowShowController)
    .controller('CrmListadavezvendedoresitensFormShowController', CrmListadavezvendedoresitensFormShowController)
    .controller('CrmListadavezvendedoresitensFormNewController', CrmListadavezvendedoresitensFormNewController)
    .controller('CrmListadavezvendedoresitensListController', CrmListadavezvendedoresitensListController)
    .service('CrmListadavezvendedoresitens', CrmListadavezvendedoresitens)
    .controller('CrmListadavezvendedoresitensFormController', CrmListadavezvendedoresitensFormController)
    .controller('CrmListadavezvendedoresitensFormDefaultController', CrmListadavezvendedoresitensFormDefaultController)
    .service('CrmListadavezvendedoresitensFormDefaultService', CrmListadavezvendedoresitensFormDefaultService)
    .controller('CrmListadavezvendedoresitensDefaultController', CrmListadavezvendedoresitensDefaultController)
    .component('crmListadavezvendedoresitensDefault', crmListadavezvendedoresitensDefault)
    .component('crmListadavezvendedoresitensShowShow', crmListadavezvendedoresitensShowShow)
    .constant('FIELDS_CrmListadavezvendedoresitens', [])
    .constant('OPTIONS_CrmListadavezvendedoresitens', { 'posicao': 'posicao', })
    .constant('MAXOCCURS_CrmListadavezvendedoresitens', {})
    .constant('SELECTS_CrmListadavezvendedoresitens', {})
    .run(['$rootScope', 'FIELDS_CrmListadavezvendedoresitens', 'OPTIONS_CrmListadavezvendedoresitens', 'MAXOCCURS_CrmListadavezvendedoresitens', 'SELECTS_CrmListadavezvendedoresitens',
        ($rootScope: any, FIELDS_CrmListadavezvendedoresitens: object, OPTIONS_CrmListadavezvendedoresitens: object, MAXOCCURS_CrmListadavezvendedoresitens: object, SELECTS_CrmListadavezvendedoresitens: object) => {
            $rootScope.OPTIONS_CrmListadavezvendedoresitens = OPTIONS_CrmListadavezvendedoresitens;
            $rootScope.MAXOCCURS_CrmListadavezvendedoresitens = MAXOCCURS_CrmListadavezvendedoresitens;
            $rootScope.SELECTS_CrmListadavezvendedoresitens = SELECTS_CrmListadavezvendedoresitens;
            $rootScope.FIELDS_CrmListadavezvendedoresitens = FIELDS_CrmListadavezvendedoresitens;
        }])
    .config(ListadavezvendedoresitensRoutes)
    .name