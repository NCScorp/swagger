import angular from "angular";
import { CrmListadavezregrasvaloresListController } from ".";
import { crmListadavezregrasvaloresDefaultShow } from "./components";
import { ListadavezregrasvaloresRoutes } from "./config";
import { FIELDS_CrmListadavezregrasvalores } from "./constant";
import { CrmListadavezregrasvaloresDefaultShowController } from "./default.form.show";
import { CrmListadavezregrasvalores } from "./factory";
import { CrmListadavezregrasvaloresFormShowController } from "./show";

export const ListadavezregrasvaloresModule = angular
    .module('ListadavezregrasvaloresModule', [])
    .controller('CrmListadavezregrasvaloresDefaultShowController', CrmListadavezregrasvaloresDefaultShowController)
    .service('CrmListadavezregrasvalores', CrmListadavezregrasvalores)
    .controller('CrmListadavezregrasvaloresListController', CrmListadavezregrasvaloresListController)
    .controller('CrmListadavezregrasvaloresFormShowController', CrmListadavezregrasvaloresFormShowController)
    .component('crmListadavezregrasvaloresDefaultShow', crmListadavezregrasvaloresDefaultShow)
    .constant('FIELDS_CrmListadavezregrasvalores', FIELDS_CrmListadavezregrasvalores)
    .run(['$rootScope', 'FIELDS_CrmListadavezregrasvalores',
        ($rootScope: any, FIELDS_CrmListadavezregrasvalores: object) => {
            $rootScope.FIELDS_CrmListadavezregrasvalores = FIELDS_CrmListadavezregrasvalores;
        }])
    .config(ListadavezregrasvaloresRoutes)
    .name