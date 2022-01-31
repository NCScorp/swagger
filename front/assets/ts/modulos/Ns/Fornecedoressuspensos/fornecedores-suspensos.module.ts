import angular from "angular";
import { NsFornecedoressuspensosListController } from ".";
import { nsFornecedoressuspensosShowShow, nsFornecedoressuspensosFornecedorreativar } from "./components";
import { FornecedoressuspensosRoutes } from "./config";
import { FIELDS_NsFornecedoressuspensos } from "./constant";
import { NsFornecedoressuspensos } from "./factory";
import { NsFornecedoressuspensosFornecedorreativarController } from "./fornecedorreativar.form";
import { NsFornecedoressuspensosFormFornecedorreativarController, NsFornecedoressuspensosFormFornecedorreativarService } from "./fornecedorreativar.modal";
import { NsFornecedoressuspensosFormShowController } from "./show";
import { NsFornecedoressuspensosShowShowController } from "./show.form.show";

export const FornecedoressuspensosModule = angular
    .module('FornecedoressuspensosModule', [])
    .service('NsFornecedoressuspensos', NsFornecedoressuspensos)
    .controller('NsFornecedoressuspensosFornecedorreativarController', NsFornecedoressuspensosFornecedorreativarController)
    .controller('NsFornecedoressuspensosFormFornecedorreativarController', NsFornecedoressuspensosFormFornecedorreativarController)
    .controller('NsFornecedoressuspensosListController', NsFornecedoressuspensosListController)
    .controller('NsFornecedoressuspensosShowShowController', NsFornecedoressuspensosShowShowController)
    .controller('NsFornecedoressuspensosFormShowController', NsFornecedoressuspensosFormShowController)
    .service('NsFornecedoressuspensosFormFornecedorreativarService', NsFornecedoressuspensosFormFornecedorreativarService)
    .component('nsFornecedoressuspensosShowShow', nsFornecedoressuspensosShowShow)
    .component('nsFornecedoressuspensosFornecedorreativar', nsFornecedoressuspensosFornecedorreativar)
    .constant('FIELDS_NsFornecedoressuspensos', FIELDS_NsFornecedoressuspensos)
    .constant('OPTIONS_NsFornecedoressuspensos', {})
    .constant('MAXOCCURS_NsFornecedoressuspensos', {})
    .constant('SELECTS_NsFornecedoressuspensos', {})
    .run(['$rootScope', 'FIELDS_NsFornecedoressuspensos', 'OPTIONS_NsFornecedoressuspensos', 'MAXOCCURS_NsFornecedoressuspensos', 'SELECTS_NsFornecedoressuspensos',
        ($rootScope: any, FIELDS_NsFornecedoressuspensos: object, OPTIONS_NsFornecedoressuspensos: object, MAXOCCURS_NsFornecedoressuspensos: object, SELECTS_NsFornecedoressuspensos: object) => {
            $rootScope.OPTIONS_NsFornecedoressuspensos = OPTIONS_NsFornecedoressuspensos;
            $rootScope.MAXOCCURS_NsFornecedoressuspensos = MAXOCCURS_NsFornecedoressuspensos;
            $rootScope.SELECTS_NsFornecedoressuspensos = SELECTS_NsFornecedoressuspensos;
            $rootScope.FIELDS_NsFornecedoressuspensos = FIELDS_NsFornecedoressuspensos;
        }])
    .config(FornecedoressuspensosRoutes)
    .name