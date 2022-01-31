import angular from "angular";
import { CrmTiposacionamentosListController } from ".";
import { crmTiposacionamentosDefaultShow, crmTiposacionamentosDefault } from "./components";
import { TiposacionamentosRoutes } from "./config";
import { FIELDS_CrmTiposacionamentos } from "./constant";
import { CrmTiposacionamentosDefaultController } from "./default.form";
import { CrmTiposacionamentosDefaultShowController } from "./default.form.show";
import { CrmTiposacionamentosFormController } from "./edit";
import { CrmTiposacionamentos } from "./factory";
import { CrmTiposacionamentosFormNewController } from "./new";
import { CrmTiposacionamentosFormShowController } from "./show";
import { ModalExclusaoTipoAcionamentoController, ModalExclusaoTipoAcionamentoService } from "./modal-exclusao";

export const TiposacionamentosModule = angular
    .module('TiposacionamentosModule', [])
    .controller('CrmTiposacionamentosDefaultShowController', CrmTiposacionamentosDefaultShowController)
    .controller('CrmTiposacionamentosDefaultController', CrmTiposacionamentosDefaultController)
    .controller('CrmTiposacionamentosFormController', CrmTiposacionamentosFormController)
    .service('CrmTiposacionamentos', CrmTiposacionamentos)
    .controller('CrmTiposacionamentosListController', CrmTiposacionamentosListController)
    .controller('CrmTiposacionamentosFormNewController', CrmTiposacionamentosFormNewController)
    .controller('CrmTiposacionamentosFormShowController', CrmTiposacionamentosFormShowController)
    .service('modalExclusaoTipoAcionamentoService', ModalExclusaoTipoAcionamentoService)
    .controller('ModalExclusaoTipoAcionamentoController', ModalExclusaoTipoAcionamentoController)
    .component('crmTiposacionamentosDefaultShow', crmTiposacionamentosDefaultShow)
    .component('crmTiposacionamentosDefault', crmTiposacionamentosDefault)
    .constant('FIELDS_CrmTiposacionamentos', FIELDS_CrmTiposacionamentos)
    .run(['$rootScope', 'FIELDS_CrmTiposacionamentos',
        ($rootScope: any, FIELDS_CrmTiposacionamentos: object) => {
            $rootScope.FIELDS_CrmTiposacionamentos = FIELDS_CrmTiposacionamentos;
        }])
    .config(TiposacionamentosRoutes)
    .name