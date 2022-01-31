import angular from "angular";
import { GpFuncoesListController } from ".";
import { gpFuncoesDefaultShow, gpFuncoesDefault } from "./components";
import { FuncoesRoutes } from "./config";
import { FIELDS_GpFuncoes } from "./constant";
import { GpFuncoesDefaultController } from "./default.form";
import { GpFuncoesDefaultShowController } from "./default.form.show";
import { GpFuncoesFormController } from "./edit";
import { GpFuncoes } from "./factory";
import { GpFuncoesFormNewController } from "./new";
import { ModalExclusaoFuncaoController, ModalExclusaoFuncaoService } from "./modal-exclusao";
import { GpFuncoesFormShowController } from "./show";

export const FuncoesModule = angular
    .module('FuncoesModule', [])
    .controller('GpFuncoesDefaultShowController', GpFuncoesDefaultShowController)
    .controller('GpFuncoesDefaultController', GpFuncoesDefaultController)
    .controller('GpFuncoesFormController', GpFuncoesFormController)
    .controller('GpFuncoesFormShowController', GpFuncoesFormShowController)
    .service('GpFuncoes', GpFuncoes)
    .controller('GpFuncoesListController', GpFuncoesListController)
    .controller('GpFuncoesFormNewController', GpFuncoesFormNewController)
    .service('modalExclusaoFuncaoService', ModalExclusaoFuncaoService)
    .controller('ModalExclusaoFuncaoController', ModalExclusaoFuncaoController)
    .component('gpFuncoesDefaultShow', gpFuncoesDefaultShow)
    .component('gpFuncoesDefault', gpFuncoesDefault)
    .controller('GpFuncoesFormNewController', GpFuncoesFormNewController)
    .constant('FIELDS_GpFuncoes', FIELDS_GpFuncoes)
    .run(['$rootScope', 'FIELDS_GpFuncoes',
        ($rootScope: any, FIELDS_GpFuncoes: object) => {
            $rootScope.FIELDS_GpFuncoes = FIELDS_GpFuncoes;
        }])
    .config(FuncoesRoutes)
    .name