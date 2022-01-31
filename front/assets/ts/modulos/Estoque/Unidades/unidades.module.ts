import angular from "angular";
import { EstoqueUnidadesListController } from ".";
import { estoqueUnidadesDefaultShow, estoqueUnidadesDefault } from "./components";
import { UnidadesRoutes } from "./config";
import { FIELDS_EstoqueUnidades } from "./constant";
import { EstoqueUnidadesDefaultController } from "./default.form";
import { EstoqueUnidadesDefaultShowController } from "./default.form.show";
import { EstoqueUnidadesFormController } from "./edit";
import { EstoqueUnidades } from "./factory";
import { EstoqueUnidadesFormNewController } from "./new";
import { EstoqueUnidadesFormShowController } from "./show";

export const UnidadesModule = angular
    .module('UnidadesModule', [])
    .controller('EstoqueUnidadesDefaultShowController', EstoqueUnidadesDefaultShowController)
    .controller('EstoqueUnidadesDefaultController', EstoqueUnidadesDefaultController)
    .controller('EstoqueUnidadesFormController', EstoqueUnidadesFormController)
    .service('EstoqueUnidades', EstoqueUnidades)
    .controller('EstoqueUnidadesListController', EstoqueUnidadesListController)
    .controller('EstoqueUnidadesFormShowController', EstoqueUnidadesFormShowController)
    .controller('EstoqueUnidadesFormNewController', EstoqueUnidadesFormNewController)
    .component('estoqueUnidadesDefaultShow', estoqueUnidadesDefaultShow)
    .component('estoqueUnidadesDefault', estoqueUnidadesDefault)
    .constant('FIELDS_EstoqueUnidades', FIELDS_EstoqueUnidades)
    .run(['$rootScope', 'FIELDS_EstoqueUnidades',
        ($rootScope: any, FIELDS_EstoqueUnidades: object) => {
            $rootScope.FIELDS_EstoqueUnidades = FIELDS_EstoqueUnidades;
        }])
    .config(UnidadesRoutes)
    .name