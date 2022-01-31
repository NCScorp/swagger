import angular from "angular";
import { CrmPropostasitensenderecosListController } from ".";
import { crmPropostasitensenderecosDefault } from "./components";
import { PropostasitensenderecosRoutes } from "./config";
import { CrmPropostasitensenderecosDefaultController } from "./default.form";
import { CrmPropostasitensenderecosFormController } from "./edit";
import { CrmPropostasitensenderecos } from "./factory";
import { CrmPropostasitensenderecosFormNewController } from "./new";

export const PropostasitensenderecosModule = angular
    .module('PropostasitensenderecosModule', [])
    .controller('CrmPropostasitensenderecosDefaultController', CrmPropostasitensenderecosDefaultController)
    .controller('CrmPropostasitensenderecosFormController', CrmPropostasitensenderecosFormController)
    .service('CrmPropostasitensenderecos', CrmPropostasitensenderecos)
    .controller('CrmPropostasitensenderecosListController', CrmPropostasitensenderecosListController)
    .controller('CrmPropostasitensenderecosFormNewController', CrmPropostasitensenderecosFormNewController)
    .component('crmPropostasitensenderecosDefault', crmPropostasitensenderecosDefault)
    .constant('FIELDS_CrmPropostasitensenderecos', [])
    .run(['$rootScope', 'FIELDS_CrmPropostasitensenderecos',
        ($rootScope: any, FIELDS_CrmPropostasitensenderecos: object) => {
            $rootScope.FIELDS_CrmPropostasitensenderecos = FIELDS_CrmPropostasitensenderecos;
        }
    ])
    .config(PropostasitensenderecosRoutes)
    .name