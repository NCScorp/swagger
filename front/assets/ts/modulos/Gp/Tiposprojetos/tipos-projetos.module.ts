import angular from "angular";
import { GpTiposprojetosListController } from ".";
import { TiposprojetosRoutes } from "./config";
import { FIELDS_GpTiposprojetos } from "./constant";
import { GpTiposprojetosFormController } from "./edit";
import { GpTiposprojetos } from "./factory";

export const TiposprojetosModule = angular
    .module('TiposprojetosModule', [])
    .controller('GpTiposprojetosFormController', GpTiposprojetosFormController)
    .service('GpTiposprojetos', GpTiposprojetos)
    .controller('GpTiposprojetosListController', GpTiposprojetosListController)
    .constant('FIELDS_GpTiposprojetos', FIELDS_GpTiposprojetos)
    .run(['$rootScope', 'FIELDS_GpTiposprojetos',
        ($rootScope: any, FIELDS_GpTiposprojetos: object) => {
            $rootScope.FIELDS_GpTiposprojetos = FIELDS_GpTiposprojetos;
        }])
    .config(TiposprojetosRoutes)
    .name