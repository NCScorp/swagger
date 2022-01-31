import angular from "angular";
import { NsPrioridadesListController } from ".";
import { nsPrioridadesShowShow, nsPrioridadesDefault, nsPrioridadesDefaultShow } from "./components";
import { PrioridadesRoutes } from "./config";
import { FIELDS_NsPrioridades } from "./constant";
import { NsPrioridadesDefaultController } from "./default.form";
import { NsPrioridadesDefaultShowController } from "./default.form.show";
import { NsPrioridadesFormController } from "./edit";
import { NsPrioridades } from "./factory";
import { ModalExclusaoService, ModalExclusaoController } from "./modal-exclusao";
import { NsPrioridadesFormNewController } from "./new";
import { NsPrioridadesFormShowController } from "./show";
import { NsPrioridadesShowShowController } from "./show.form.show";

export const PrioridadesModule = angular
    .module('PrioridadesModule', [])
    .controller('NsPrioridadesDefaultShowController', NsPrioridadesDefaultShowController)
    .controller('NsPrioridadesDefaultController', NsPrioridadesDefaultController)
    .controller('NsPrioridadesFormController', NsPrioridadesFormController)
    .service('NsPrioridades', NsPrioridades)
    .controller('NsPrioridadesListController', NsPrioridadesListController)
    .service('modalExclusaoService', ModalExclusaoService)
    .controller('NsPrioridadesFormNewController', NsPrioridadesFormNewController)
    .controller('NsPrioridadesShowShowController', NsPrioridadesShowShowController)
    .controller('NsPrioridadesFormShowController', NsPrioridadesFormShowController)
    .controller('ModalExclusaoController', ModalExclusaoController)
    .component('nsPrioridadesShowShow', nsPrioridadesShowShow)
    .component('nsPrioridadesDefault', nsPrioridadesDefault)
    .component('nsPrioridadesDefaultShow', nsPrioridadesDefaultShow)
    .constant('FIELDS_NsPrioridades', FIELDS_NsPrioridades)
    .run(['$rootScope', 'FIELDS_NsPrioridades',
        ($rootScope: any, FIELDS_NsPrioridades: object) => {
            $rootScope.FIELDS_NsPrioridades = FIELDS_NsPrioridades;
        }])
    .config(PrioridadesRoutes)
    .name