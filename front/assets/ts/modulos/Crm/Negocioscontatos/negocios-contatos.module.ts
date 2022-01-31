import angular from "angular";
import { CrmNegocioscontatosListController } from ".";
import { crmNegocioscontatosDefault } from "./components";
import { NegocioscontatosRoutes } from "./config";
import { CrmNegocioscontatosDefaultController } from "./default.form";
import { CrmNegocioscontatosFormDefaultController, CrmNegocioscontatosFormDefaultService } from "./delete.modal";
import { CrmNegocioscontatosFormController } from "./edit";
import { CrmNegocioscontatos } from "./factory";
import { CrmNegocioscontatosFormNewController } from "./new";

export const NegocioscontatosModule = angular
    .module('NegocioscontatosModule', [])
    .component('crmNegocioscontatosDefault', crmNegocioscontatosDefault)
    .constant('FIELDS_CrmNegocioscontatos', [])
    .service('CrmNegocioscontatos', CrmNegocioscontatos)
    .controller('CrmNegocioscontatosListController', CrmNegocioscontatosListController)
    .controller('CrmNegocioscontatosFormNewController', CrmNegocioscontatosFormNewController)
    .controller('CrmNegocioscontatosDefaultController', CrmNegocioscontatosDefaultController)
    .controller('CrmNegocioscontatosFormDefaultController', CrmNegocioscontatosFormDefaultController)
    .controller('CrmNegocioscontatosFormController', CrmNegocioscontatosFormController)
    .service('CrmNegocioscontatosFormDefaultService', CrmNegocioscontatosFormDefaultService)
    .run(['$rootScope', 'FIELDS_CrmNegocioscontatos',
        ($rootScope: any, FIELDS_CrmNegocioscontatos: object) => {
            $rootScope.FIELDS_CrmNegocioscontatos = FIELDS_CrmNegocioscontatos;
        }])
    .config(NegocioscontatosRoutes)
    .name