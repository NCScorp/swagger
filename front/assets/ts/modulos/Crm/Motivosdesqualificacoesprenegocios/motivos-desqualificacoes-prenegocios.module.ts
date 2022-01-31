import angular from "angular";
import { CrmMotivosdesqualificacoesprenegociosListController } from ".";
import { crmMotivosdesqualificacoesprenegociosDefaultShow } from "./components";
import { MotivosdesqualificacoesprenegociosRoutes } from "./config";
import { FIELDS_CrmMotivosdesqualificacoesprenegocios } from "./constant";
import { CrmMotivosdesqualificacoesprenegociosDefaultShowController } from "./default.form.show";
import { CrmMotivosdesqualificacoesprenegocios } from "./factory";
import { CrmMotivosdesqualificacoesprenegociosFormShowController } from "./show";

export const MotivosdesqualificacoesprenegociosModule = angular
    .module('MotivosdesqualificacoesprenegociosModule', [])
    .controller('CrmMotivosdesqualificacoesprenegociosDefaultShowController', CrmMotivosdesqualificacoesprenegociosDefaultShowController)
    .service('CrmMotivosdesqualificacoesprenegocios', CrmMotivosdesqualificacoesprenegocios)
    .controller('CrmMotivosdesqualificacoesprenegociosListController', CrmMotivosdesqualificacoesprenegociosListController)
    .controller('CrmMotivosdesqualificacoesprenegociosFormShowController', CrmMotivosdesqualificacoesprenegociosFormShowController)
    .run(['$rootScope', 'FIELDS_CrmMotivosdesqualificacoesprenegocios',
        ($rootScope: any, FIELDS_CrmMotivosdesqualificacoesprenegocios: object) => {
            $rootScope.FIELDS_CrmMotivosdesqualificacoesprenegocios = FIELDS_CrmMotivosdesqualificacoesprenegocios;
        }])
    .constant('FIELDS_CrmMotivosdesqualificacoesprenegocios', FIELDS_CrmMotivosdesqualificacoesprenegocios)
    .component('crmMotivosdesqualificacoesprenegociosDefaultShow', crmMotivosdesqualificacoesprenegociosDefaultShow)
    .config(MotivosdesqualificacoesprenegociosRoutes)
    .name