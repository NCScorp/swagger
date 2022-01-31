import angular from "angular";
import { NsClientesListController } from ".";
import { nsClientesShowShow, nsClientesDefault } from "./components";
import { ClientesRoutes } from "./config";
import { FIELDS_NsClientes } from "./constant";
import { NsClientesDefaultController } from "./default.form";
import { NsClientesFormController } from "./edit";
import { NsClientes } from "./factory";
import { NsClientesFormNewController } from "./new";
import { NsClientesFormShowController } from "./show";
import { NsClientesShowShowController } from "./show.form.show";

export const ClientesModule = angular
    .module('ClientesModule', [])
    .controller('NsClientesDefaultController', NsClientesDefaultController)
    .controller('NsClientesFormController', NsClientesFormController)
    .service('NsClientes', NsClientes)
    .controller('NsClientesListController', NsClientesListController)
    .controller('NsClientesFormNewController', NsClientesFormNewController)
    .controller('NsClientesShowShowController', NsClientesShowShowController)
    .controller('NsClientesFormShowController', NsClientesFormShowController)
    .component('nsClientesShowShow', nsClientesShowShow)
    .component('nsClientesDefault', nsClientesDefault)
    .constant('OPTIONS_NsClientes', { 'tipo': 'tipo', 'cnpj': 'cnpj', })
    .constant('MAXOCCURS_NsClientes', {})
    .constant('SELECTS_NsClientes', {})
    .constant('FIELDS_NsClientes', FIELDS_NsClientes)
    .run(['$rootScope', 'FIELDS_NsClientes', 'OPTIONS_NsClientes', 'MAXOCCURS_NsClientes', 'SELECTS_NsClientes',
        ($rootScope: any, FIELDS_NsClientes: object, OPTIONS_NsClientes: object, MAXOCCURS_NsClientes: object, SELECTS_NsClientes: object) => {
            $rootScope.OPTIONS_NsClientes = OPTIONS_NsClientes;
            $rootScope.MAXOCCURS_NsClientes = MAXOCCURS_NsClientes;
            $rootScope.SELECTS_NsClientes = SELECTS_NsClientes;
            $rootScope.FIELDS_NsClientes = FIELDS_NsClientes;
        }])
    .config(ClientesRoutes)
    .name