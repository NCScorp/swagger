import angular from "angular";
import { CrmConfiguracoestaxasadministrativasListController } from ".";
import { ConfiguracoestaxasadministrativasRoutes } from "./config";
import { FIELDS_CrmConfiguracoestaxasadministrativas } from "./constant";
import { CrmConfiguracoestaxasadministrativasDefaultController } from "./default.form";
import { CrmConfiguracoestaxasadministrativasDefaultShowController } from "./default.form.show";
import { CrmConfiguracoestaxasadministrativasFormController } from "./edit";
import { CrmConfiguracoestaxasadministrativas } from "./factory";
import { ModalExclusaoTaxaAdmService, ModalExclusaoTaxaAdmController } from "./modal-exclusao";
import { CrmConfiguracoestaxasadministrativasFormNewController } from "./new";
import { CrmConfiguracoestaxasadministrativasFormShowController } from "./show";
import { crmConfiguracoestaxasadministrativasDefaultShow, crmConfiguracoestaxasadministrativasDefault } from "./components";

export const ConfiguracoestaxasadministrativasModule = angular
    .module('ConfiguracoestaxasadministrativasModule', [])
    .controller('CrmConfiguracoestaxasadministrativasFormNewController', CrmConfiguracoestaxasadministrativasFormNewController)
    .controller('CrmConfiguracoestaxasadministrativasFormShowController', CrmConfiguracoestaxasadministrativasFormShowController)
    .controller('CrmConfiguracoestaxasadministrativasListController', CrmConfiguracoestaxasadministrativasListController)
    .service('ModalExclusaoTaxaAdmService', ModalExclusaoTaxaAdmService)
    .controller('ModalExclusaoTaxaAdmController', ModalExclusaoTaxaAdmController)
    .service('CrmConfiguracoestaxasadministrativas', CrmConfiguracoestaxasadministrativas)
    .controller('CrmConfiguracoestaxasadministrativasFormController', CrmConfiguracoestaxasadministrativasFormController)
    .controller('CrmConfiguracoestaxasadministrativasDefaultController', CrmConfiguracoestaxasadministrativasDefaultController)
    .controller('CrmConfiguracoestaxasadministrativasDefaultShowController', CrmConfiguracoestaxasadministrativasDefaultShowController)
    .component('crmConfiguracoestaxasadministrativasDefaultShow', crmConfiguracoestaxasadministrativasDefaultShow)
    .component('crmConfiguracoestaxasadministrativasDefault', crmConfiguracoestaxasadministrativasDefault)
    .constant('FIELDS_CrmConfiguracoestaxasadministrativas', FIELDS_CrmConfiguracoestaxasadministrativas)
    .run(['$rootScope', 'FIELDS_CrmConfiguracoestaxasadministrativas',
        ($rootScope: any, FIELDS_CrmConfiguracoestaxasadministrativas: object) => {
            $rootScope.FIELDS_CrmConfiguracoestaxasadministrativas = FIELDS_CrmConfiguracoestaxasadministrativas;
        }])
    .config(ConfiguracoestaxasadministrativasRoutes)
    .name
