import angular from "angular";
import { CrmListadavezregrasListController } from ".";
import { crmListadavezregrasDefaultShow } from "./components";
import { ListadavezregrasRoutes } from "./config";
import { FIELDS_CrmListadavezregras } from "./constant";
import { CrmListadavezregrasDefaultShowController } from "./default.form.show";
import { CrmListadavezregras } from "./factory";
import { CrmListadavezregrasFormShowController } from "./show";

export const ListadavezregrasModule = angular
    .module('ListadavezregrasModule', [])
    .controller('CrmListadavezregrasListController', CrmListadavezregrasListController)
    .controller('CrmListadavezregrasFormShowController', CrmListadavezregrasFormShowController)
    .service('CrmListadavezregras', CrmListadavezregras)
    .controller('CrmListadavezregrasDefaultShowController', CrmListadavezregrasDefaultShowController)
    .component('crmListadavezregrasDefaultShow', crmListadavezregrasDefaultShow)
    .constant('FIELDS_CrmListadavezregras', FIELDS_CrmListadavezregras)
    .run(['$rootScope', 'FIELDS_CrmListadavezregras',
        ($rootScope: any, FIELDS_CrmListadavezregras: object) => {
            $rootScope.FIELDS_CrmListadavezregras = FIELDS_CrmListadavezregras;
        }])
    .config(ListadavezregrasRoutes)
    .name