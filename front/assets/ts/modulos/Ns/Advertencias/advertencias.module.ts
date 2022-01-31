import angular from "angular";
import { NsAdvertenciasListController } from ".";
import { NsAdvertenciasArquivarController } from "./arquivar.form";
import { NsAdvertenciasFormArquivarController, NsAdvertenciasFormArquivarService } from "./arquivar.modal";
import { nsAdvertenciasShowShow, nsAdvertenciasArquivar, nsAdvertenciasExcluir } from "./components";
import { AdvertenciasRoutes } from "./config";
import { FIELDS_NsAdvertencias } from "./constant";
import { NsAdvertenciasExcluirController } from "./excluir.form";
import { NsAdvertenciasFormExcluirController, NsAdvertenciasFormExcluirService } from "./excluir.modal";
import { NsAdvertencias } from "./factory";
import { NsAdvertenciasFormShowController } from "./show";
import { NsAdvertenciasShowShowController } from "./show.form.show";

export const AdvertenciasModule = angular
    .module('AdvertenciasModule', [])
    .controller('NsAdvertenciasExcluirController', NsAdvertenciasExcluirController)
    .controller('NsAdvertenciasFormExcluirController', NsAdvertenciasFormExcluirController)
    .service('NsAdvertenciasFormExcluirService', NsAdvertenciasFormExcluirService)
    .service('NsAdvertencias', NsAdvertencias)
    .controller('NsAdvertenciasListController', NsAdvertenciasListController)
    .controller('NsAdvertenciasShowShowController', NsAdvertenciasShowShowController)
    .controller('NsAdvertenciasFormShowController', NsAdvertenciasFormShowController)
    .component('nsAdvertenciasShowShow', nsAdvertenciasShowShow)
    .component('nsAdvertenciasArquivar', nsAdvertenciasArquivar)
    .component('nsAdvertenciasExcluir', nsAdvertenciasExcluir)
    .controller('NsAdvertenciasArquivarController', NsAdvertenciasArquivarController)
    .controller('NsAdvertenciasFormArquivarController', NsAdvertenciasFormArquivarController)
    .service('NsAdvertenciasFormArquivarService', NsAdvertenciasFormArquivarService)
    .constant('OPTIONS_NsAdvertencias', {})
    .constant('MAXOCCURS_NsAdvertencias', {})
    .constant('SELECTS_NsAdvertencias', {})
    .constant('FIELDS_NsAdvertencias', FIELDS_NsAdvertencias)
    .run(['$rootScope', 'FIELDS_NsAdvertencias', 'OPTIONS_NsAdvertencias', 'MAXOCCURS_NsAdvertencias', 'SELECTS_NsAdvertencias',
        ($rootScope: any, FIELDS_NsAdvertencias: object, OPTIONS_NsAdvertencias: object, MAXOCCURS_NsAdvertencias: object, SELECTS_NsAdvertencias: object) => {
            $rootScope.OPTIONS_NsAdvertencias = OPTIONS_NsAdvertencias;
            $rootScope.MAXOCCURS_NsAdvertencias = MAXOCCURS_NsAdvertencias;
            $rootScope.SELECTS_NsAdvertencias = SELECTS_NsAdvertencias;
            $rootScope.FIELDS_NsAdvertencias = FIELDS_NsAdvertencias;
        }])
    .config(AdvertenciasRoutes)
    .name