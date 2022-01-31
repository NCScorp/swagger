import angular from "angular";
import { NsEstabelecimentosListController } from ".";
import { EstabelecimentosRoutes } from "./config";
import { FIELDS_NsEstabelecimentos } from "./constant";
import { NsEstabelecimentos } from "./factory";

export const EstabelecimentosModule = angular
    .module('EstabelecimentosModule', [])
    .constant('FIELDS_NsEstabelecimentos', FIELDS_NsEstabelecimentos)
    .controller('NsEstabelecimentosListController', NsEstabelecimentosListController)
    .service('NsEstabelecimentos', NsEstabelecimentos)
    .run(['$rootScope', 'FIELDS_NsEstabelecimentos',
        ($rootScope: any, FIELDS_NsEstabelecimentos: object) => {
            $rootScope.FIELDS_NsEstabelecimentos = FIELDS_NsEstabelecimentos;
        }])
    .config(EstabelecimentosRoutes)
    .name