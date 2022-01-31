import angular from "angular";
import { NsTiposatividadesListController } from ".";
import { nsTiposatividadesDefaultShow, nsTiposatividadesDefault } from "./components";
import { TiposatividadesRoutes } from "./config";
import { FIELDS_NsTiposatividades } from "./constant";
import { NsTiposatividadesDefaultController } from "./default.form";
import { NsTiposatividadesDefaultShowController } from "./default.form.show";
import { NsTiposatividadesFormController } from "./edit";
import { NsTiposatividades } from "./factory";
import { NsTiposatividadesFormNewController } from "./new";
import { NsTiposatividadesFormShowController } from "./show";

export const TiposatividadesModule = angular
    .module('TiposatividadesModule', [])
    .controller('NsTiposatividadesDefaultShowController', NsTiposatividadesDefaultShowController)
    .controller('NsTiposatividadesDefaultController', NsTiposatividadesDefaultController)
    .controller('NsTiposatividadesFormController', NsTiposatividadesFormController)
    .service('NsTiposatividades', NsTiposatividades)
    .controller('NsTiposatividadesListController', NsTiposatividadesListController)
    .controller('NsTiposatividadesFormNewController', NsTiposatividadesFormNewController)
    .controller('NsTiposatividadesFormShowController', NsTiposatividadesFormShowController)
    .component('nsTiposatividadesDefaultShow', nsTiposatividadesDefaultShow)
    .component('nsTiposatividadesDefault', nsTiposatividadesDefault)
    .constant('FIELDS_NsTiposatividades', FIELDS_NsTiposatividades)
    .run(['$rootScope', 'FIELDS_NsTiposatividades',
        ($rootScope: any, FIELDS_NsTiposatividades: object) => {
            $rootScope.FIELDS_NsTiposatividades = FIELDS_NsTiposatividades;
        }])
    .config(TiposatividadesRoutes)
    .name