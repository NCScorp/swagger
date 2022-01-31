import angular from "angular";
import { NsFollowupsnegociosListController } from ".";
import { nsFollowupsnegociosDefault } from "./components";
import { FollowupsnegociosRoutes } from "./config";
import { FIELDS_NsFollowupsnegocios } from "./constant";
import { NsFollowupsnegociosDefaultController } from "./default.form";
import { NsFollowupsnegociosFormController } from "./edit";
import { NsFollowupsnegocios } from "./factory";
import { NsModalAnexoService, NsModalAnexoController } from "./modal-anexo";
import { NsFollowupsnegociosFormNewController } from "./new";

export const FollowupsnegociosModule = angular
    .module('FollowupsnegociosModule', [])
    .controller('NsFollowupsnegociosDefaultController', NsFollowupsnegociosDefaultController)
    .controller('NsFollowupsnegociosFormController', NsFollowupsnegociosFormController)
    .service('NsFollowupsnegocios', NsFollowupsnegocios)
    .controller('NsFollowupsnegociosListController', NsFollowupsnegociosListController)
    .service('NsModalAnexoService', NsModalAnexoService)
    .controller('NsFollowupsnegociosFormNewController', NsFollowupsnegociosFormNewController)
    .controller('NsModalAnexoController', NsModalAnexoController)
    .component('nsFollowupsnegociosDefault', nsFollowupsnegociosDefault)
    .constant('FIELDS_NsFollowupsnegocios', FIELDS_NsFollowupsnegocios)
    .run(['$rootScope', 'FIELDS_NsFollowupsnegocios',
        ($rootScope: any, FIELDS_NsFollowupsnegocios: object) => {
            $rootScope.FIELDS_NsFollowupsnegocios = FIELDS_NsFollowupsnegocios;
        }])
    .config(FollowupsnegociosRoutes)
    .name