import angular from "angular";
import { GpCustosListController } from ".";
import { gpCustosDefault } from "./components";
import { CustosRoutes } from "./config";
import { FIELDS_GpCustos } from "./constant";
import { GpCustosDefaultController } from "./default.form";
import { GpCustosFormController } from "./edit";
import { GpCustos } from "./factory";
import { GpCustosFormNewController } from "./new";

export const CustosModule = angular
    .module('CustosModule', [])
    .controller('GpCustosDefaultController', GpCustosDefaultController)
    .controller('GpCustosFormController', GpCustosFormController)
    .service('GpCustos', GpCustos)
    .controller('GpCustosListController', GpCustosListController)
    .controller('GpCustosFormNewController', GpCustosFormNewController)
    .component('gpCustosDefault', gpCustosDefault)
    .constant('FIELDS_GpCustos',FIELDS_GpCustos)
    .run(['$rootScope', 'FIELDS_GpCustos',
        ($rootScope: any, FIELDS_GpCustos: object) => {
            $rootScope.FIELDS_GpCustos = FIELDS_GpCustos;
        }])
    .config(CustosRoutes)
    .name