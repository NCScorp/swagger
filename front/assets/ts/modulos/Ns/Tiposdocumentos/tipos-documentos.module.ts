import angular from "angular";
import { NsTiposdocumentosListController } from ".";
import { nsTiposdocumentosDefaultShow, nsTiposdocumentosDefault } from "./components";
import { TiposdocumentosRoutes } from "./config";
import { FIELDS_NsTiposdocumentos } from "./constant";
import { NsTiposdocumentosDefaultController } from "./default.form";
import { NsTiposdocumentosDefaultShowController } from "./default.form.show";
import { NsTiposdocumentosFormController } from "./edit";
import { NsTiposdocumentos } from "./factory";
import { NsTiposdocumentosFormNewController } from "./new";
import { NsTiposdocumentosFormShowController } from "./show";

export const TiposdocumentosModule = angular
    .module('TiposdocumentosModule', [])
    .controller('NsTiposdocumentosListController', NsTiposdocumentosListController)
    .controller('NsTiposdocumentosFormNewController', NsTiposdocumentosFormNewController)
    .controller('NsTiposdocumentosFormShowController', NsTiposdocumentosFormShowController)
    .controller('NsTiposdocumentosDefaultShowController', NsTiposdocumentosDefaultShowController)
    .controller('NsTiposdocumentosDefaultController', NsTiposdocumentosDefaultController)
    .controller('NsTiposdocumentosFormController', NsTiposdocumentosFormController)
    .service('NsTiposdocumentos', NsTiposdocumentos)
    .component('nsTiposdocumentosDefaultShow', nsTiposdocumentosDefaultShow)
    .component('nsTiposdocumentosDefault', nsTiposdocumentosDefault)
    .constant('FIELDS_NsTiposdocumentos', FIELDS_NsTiposdocumentos)
    .constant('OPTIONS_NsTiposdocumentos', {})
    .constant('MAXOCCURS_NsTiposdocumentos', {})
    .constant('SELECTS_NsTiposdocumentos', {})
    .run(['$rootScope', 'FIELDS_NsTiposdocumentos', 'OPTIONS_NsTiposdocumentos', 'MAXOCCURS_NsTiposdocumentos', 'SELECTS_NsTiposdocumentos',
        ($rootScope: any, FIELDS_NsTiposdocumentos: object, OPTIONS_NsTiposdocumentos: object, MAXOCCURS_NsTiposdocumentos: object, SELECTS_NsTiposdocumentos: object) => {
            $rootScope.OPTIONS_NsTiposdocumentos = OPTIONS_NsTiposdocumentos;
            $rootScope.MAXOCCURS_NsTiposdocumentos = MAXOCCURS_NsTiposdocumentos;
            $rootScope.SELECTS_NsTiposdocumentos = SELECTS_NsTiposdocumentos;
            $rootScope.FIELDS_NsTiposdocumentos = FIELDS_NsTiposdocumentos;
        }])
    .config(TiposdocumentosRoutes)
    .name