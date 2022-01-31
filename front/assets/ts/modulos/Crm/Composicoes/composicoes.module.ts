import angular from "angular";
import { CrmComposicoesListController } from ".";
import { crmComposicoesDefaultShow, crmComposicoesDefault } from "./components";
import { composicoesRoutes } from "./config";
import { FIELDS_CrmComposicoes } from "./constant";
import { CrmComposicoesDefaultController } from "./default.form";
import { CrmComposicoesDefaultShowController } from "./default.form.show";
import { CrmComposicoesFormController } from "./edit";
import { CrmComposicoes } from "./factory";
import { CrmComposicoesFormModalController, CrmComposicoesFormService } from "./modal";
import { CrmComposicoesFormNewController } from "./new";
import { CrmComposicoesFormShowController } from "./show";

export const composicoesModule = angular
    .module('composicoesModule', [])
    .service('CrmComposicoes', CrmComposicoes)
    .controller('CrmComposicoesFormShowController', CrmComposicoesFormShowController)
    .controller('CrmComposicoesFormNewController', CrmComposicoesFormNewController)
    .controller('CrmComposicoesFormModalController', CrmComposicoesFormModalController)
    .service('CrmComposicoesFormService', CrmComposicoesFormService)
    .controller('CrmComposicoesListController', CrmComposicoesListController)
    .controller('CrmComposicoesFormController', CrmComposicoesFormController)
    .controller('CrmComposicoesDefaultController', CrmComposicoesDefaultController)
    .controller('CrmComposicoesDefaultShowController', CrmComposicoesDefaultShowController)
    .component('crmComposicoesDefaultShow', crmComposicoesDefaultShow)
    .component('crmComposicoesDefault', crmComposicoesDefault)
    .constant('FIELDS_CrmComposicoes',FIELDS_CrmComposicoes)
    .run(['$rootScope', 'FIELDS_CrmComposicoes',
        ($rootScope: any, FIELDS_CrmComposicoes: object) => {
            $rootScope.FIELDS_CrmComposicoes = FIELDS_CrmComposicoes;
        }])
    .config(composicoesRoutes)
    .name