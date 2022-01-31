import angular from "angular";
import { NsCamposcustomizadosListController } from ".";
import { nsCamposcustomizadosDefault } from "./components";
import { NsCamposcustomizadosRoutes } from "./config";
import { FIELDS_NsCamposcustomizados } from "./constant";
import { NsCamposcustomizadosDefaultController } from "./default.form";
import { NsCamposcustomizadosFormController } from "./edit";
import { NsCamposcustomizados } from "./factory";
import { NsCamposcustomizadosFormNewController } from "./new";

export const NsCamposcustomizadosModule = angular
    .module('NsCamposcustomizadosModule', [])
    .controller('NsCamposcustomizadosDefaultController', NsCamposcustomizadosDefaultController)
    .controller('NsCamposcustomizadosFormController', NsCamposcustomizadosFormController)
    .service('NsCamposcustomizados', NsCamposcustomizados)
    .controller('NsCamposcustomizadosListController', NsCamposcustomizadosListController)
    .controller('NsCamposcustomizadosFormNewController', NsCamposcustomizadosFormNewController)
    .component('nsCamposcustomizadosDefault', nsCamposcustomizadosDefault)
    .constant('FIELDS_NsCamposcustomizados', FIELDS_NsCamposcustomizados)
    .constant('OPTIONS_NsCamposcustomizados', { 'crmnegocio': 'crmnegocio', 'crmnegociosvisualizacao': 'crmnegociosvisualizacao', 'crmnegocioexibenalistagem': 'crmnegocioexibenalistagem', 'secao': 'secao', })
    .constant('MAXOCCURS_NsCamposcustomizados', { 'crmnegocio': 1, 'crmnegociosvisualizacao': 1, 'crmnegocioexibenalistagem': 1, 'secao': 1, })
    .constant('SELECTS_NsCamposcustomizados', {})
    .run(['$rootScope', 'FIELDS_NsCamposcustomizados', 'OPTIONS_NsCamposcustomizados', 'MAXOCCURS_NsCamposcustomizados', 'SELECTS_NsCamposcustomizados',
        ($rootScope: any, FIELDS_NsCamposcustomizados: object, OPTIONS_NsCamposcustomizados: object, MAXOCCURS_NsCamposcustomizados: object, SELECTS_NsCamposcustomizados: object) => {
            $rootScope.OPTIONS_NsCamposcustomizados = OPTIONS_NsCamposcustomizados;
            $rootScope.MAXOCCURS_NsCamposcustomizados = MAXOCCURS_NsCamposcustomizados;
            $rootScope.SELECTS_NsCamposcustomizados = SELECTS_NsCamposcustomizados;
            $rootScope.FIELDS_NsCamposcustomizados = FIELDS_NsCamposcustomizados;
        }])
    .config(NsCamposcustomizadosRoutes)
    .name