import angular from "angular";
import { NsPessoasLogosListController } from ".";
import { nsPessoasLogosDefault } from "./components";
import { PessoasLogoRoutes } from "./config";
import { NsPessoasLogosDefaultController } from "./default.form";
import { NsPessoasLogosFormController } from "./edit";
import { NsPessoasLogos } from "./factory";
import { NsPessoasLogosFormNewController } from "./new";

export const PessoasLogoModule = angular
    .module('PessoasLogoModule', [])
    .controller('NsPessoasLogosFormController', NsPessoasLogosFormController)
    .service('NsPessoasLogos', NsPessoasLogos)
    .controller('NsPessoasLogosListController', NsPessoasLogosListController)
    .controller('NsPessoasLogosFormNewController', NsPessoasLogosFormNewController)
    .controller('NsPessoasLogosDefaultController', NsPessoasLogosDefaultController)
    .component('nsPessoasLogosDefault', nsPessoasLogosDefault)
    .constant('FIELDS_NsPessoasLogos', [])
    .run(['$rootScope', 'FIELDS_NsPessoasLogos',
        ($rootScope: any, FIELDS_NsPessoasLogos: object) => {
            $rootScope.FIELDS_NsPessoasLogos = FIELDS_NsPessoasLogos;
        }])
    .config(PessoasLogoRoutes)

    .name