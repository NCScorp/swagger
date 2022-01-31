import angular from "angular";
import { CrmFornecedoresenvolvidosanexosListController } from ".";
import { crmFornecedoresenvolvidosanexosDefault } from "./components";
import { FornecedoresenvolvidosanexosRoutes } from "./config";
import { CrmFornecedoresenvolvidosanexosDefaultController } from "./default.form";
import { CrmFornecedoresenvolvidosanexosFormController } from "./edit";
import { CrmFornecedoresenvolvidosanexos } from "./factory";
import { CrmFornecedoresenvolvidosanexosFormNewController } from "./new";

export const FornecedoresenvolvidosanexosModule = angular
    .module('FornecedoresenvolvidosanexosModule', [])
    .controller('CrmFornecedoresenvolvidosanexosDefaultController', CrmFornecedoresenvolvidosanexosDefaultController)
    .controller('CrmFornecedoresenvolvidosanexosFormController', CrmFornecedoresenvolvidosanexosFormController)
    .service('CrmFornecedoresenvolvidosanexos', CrmFornecedoresenvolvidosanexos)
    .controller('CrmFornecedoresenvolvidosanexosListController', CrmFornecedoresenvolvidosanexosListController)
    .controller('CrmFornecedoresenvolvidosanexosFormNewController', CrmFornecedoresenvolvidosanexosFormNewController)
    .component('crmFornecedoresenvolvidosanexosDefault', crmFornecedoresenvolvidosanexosDefault)
    .constant('FIELDS_CrmFornecedoresenvolvidosanexos', [
    ])
    .run(['$rootScope', 'FIELDS_CrmFornecedoresenvolvidosanexos',
        ($rootScope: any, FIELDS_CrmFornecedoresenvolvidosanexos: object) => {
            $rootScope.FIELDS_CrmFornecedoresenvolvidosanexos = FIELDS_CrmFornecedoresenvolvidosanexos;
        }])
    .config(FornecedoresenvolvidosanexosRoutes)
    .name