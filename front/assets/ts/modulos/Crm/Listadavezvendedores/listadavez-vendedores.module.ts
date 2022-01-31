import angular from "angular";
import { CrmListadavezvendedoresListController } from ".";
import { crmListadavezvendedoresShowShow, crmListadavezvendedoresDefault } from "./components";
import { ListadavezvendedoresRoutes } from "./config";
import { ListadavezvendedoresFullRoutes } from "./config-listadavez.full";
import { CrmListadavezvendedoresDefaultController } from "./default.form";
import { CrmListadavezvendedoresFormController } from "./edit";
import { CrmListadavezvendedores } from "./factory";
import { CrmListaDaVezFullController } from "./listadavez.full";
import { CrmListaDaVezListasController } from "./listadavez.listas";
import { CrmListaDaVezListasModalService, CrmListaDaVezListasModalController } from "./listadavez.listas.modal";
import { CrmListaDaVezRegrasController } from "./listadavez.regras";
import { CrmListadavezvendedoresFormNewController } from "./new";
import { CrmListadavezvendedoresFormShowController } from "./show";
import { CrmListadavezvendedoresShowShowController } from "./show.form.show";

export const ListadavezvendedoresModule = angular
    .module('ListadavezvendedoresModule', [])
    .controller('CrmListadavezvendedoresDefaultController', CrmListadavezvendedoresDefaultController)
    .controller('CrmListadavezvendedoresFormController', CrmListadavezvendedoresFormController)
    .service('CrmListadavezvendedores', CrmListadavezvendedores)
    .service('CrmListaDaVezListasModalService', CrmListaDaVezListasModalService)
    .controller('CrmListaDaVezListasModalController', CrmListaDaVezListasModalController)
    .controller('CrmListaDaVezListasController', CrmListaDaVezListasController)
    .controller('CrmListaDaVezRegrasController', CrmListaDaVezRegrasController)
    .controller('CrmListadavezvendedoresFormNewController', CrmListadavezvendedoresFormNewController)
    .controller('CrmListadavezvendedoresShowShowController', CrmListadavezvendedoresShowShowController)
    .controller('CrmListadavezvendedoresFormShowController', CrmListadavezvendedoresFormShowController)
    .controller('CrmListadavezvendedoresListController', CrmListadavezvendedoresListController)
    .controller('CrmListaDaVezFullController', CrmListaDaVezFullController)
    .component('crmListadavezvendedoresShowShow', crmListadavezvendedoresShowShow)
    .component('crmListadavezvendedoresDefault', crmListadavezvendedoresDefault)
    .constant('FIELDS_CrmListadavezvendedores', [
    ])
    .run(['$rootScope', 'FIELDS_CrmListadavezvendedores',
        ($rootScope: any, FIELDS_CrmListadavezvendedores: object) => {
            $rootScope.FIELDS_CrmListadavezvendedores = FIELDS_CrmListadavezvendedores;
        }])
    .config(ListadavezvendedoresFullRoutes)
    .config(ListadavezvendedoresRoutes)
    .name