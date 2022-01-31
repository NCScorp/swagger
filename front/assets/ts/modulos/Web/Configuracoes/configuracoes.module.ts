import angular from "angular";
import { WebConfiguracoesListController } from ".";
import { webConfiguracoesDefaultShow } from "./components";
import { ConfiguracoesRoutes } from "./config";
import { FIELDS_WebConfiguracoes } from "./constant";
import { WebConfiguracoesDefaultShowController } from "./default.form.show";
import { WebConfiguracoes } from "./factory";
import { WebConfiguracoesFormShowController } from "./show";

export const ConfiguracoesModule = angular
    .module('ConfiguracoesModule', [])
    .controller('WebConfiguracoesDefaultShowController', WebConfiguracoesDefaultShowController)
    .service('WebConfiguracoes', WebConfiguracoes)
    .controller('WebConfiguracoesListController', WebConfiguracoesListController)
    .controller('WebConfiguracoesFormShowController', WebConfiguracoesFormShowController)
    .component('webConfiguracoesDefaultShow', webConfiguracoesDefaultShow)
    .constant('FIELDS_WebConfiguracoes', FIELDS_WebConfiguracoes)
    .constant('OPTIONS_WebConfiguracoes', { 'chave': 'chave', 'valor': 'valor', 'sistema': 'sistema', })
    .constant('MAXOCCURS_WebConfiguracoes', { 'chave': 1, 'valor': 1, 'sistema': 1, })
    .constant('SELECTS_WebConfiguracoes', {})
    .run(['$rootScope', 'FIELDS_WebConfiguracoes', 'OPTIONS_WebConfiguracoes', 'MAXOCCURS_WebConfiguracoes', 'SELECTS_WebConfiguracoes',
        ($rootScope: any, FIELDS_WebConfiguracoes: object, OPTIONS_WebConfiguracoes: object, MAXOCCURS_WebConfiguracoes: object, SELECTS_WebConfiguracoes: object) => {
            $rootScope.OPTIONS_WebConfiguracoes = OPTIONS_WebConfiguracoes;
            $rootScope.MAXOCCURS_WebConfiguracoes = MAXOCCURS_WebConfiguracoes;
            $rootScope.SELECTS_WebConfiguracoes = SELECTS_WebConfiguracoes;
            $rootScope.FIELDS_WebConfiguracoes = FIELDS_WebConfiguracoes;
        }])
    .config(ConfiguracoesRoutes)
    .name