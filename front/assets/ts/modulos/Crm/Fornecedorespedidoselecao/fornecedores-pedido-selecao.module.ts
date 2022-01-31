import angular from "angular";
import { CrmFornecedorespedidoselecaoListController } from ".";
import { FornecedorespedidoselecaoRoutes } from "./config";
import { OPTIONS_CrmFornecedorespedidoselecao, MAXOCCURS_CrmFornecedorespedidoselecao, SELECTS_CrmFornecedorespedidoselecao, FIELDS_CrmFornecedorespedidoselecao } from "./constant";
import { CrmFornecedorespedidoselecao } from "./factory";

export const FornecedorespedidoselecaoModule = angular
    .module('FornecedorespedidoselecaoModule', [])
    .service('CrmFornecedorespedidoselecao', CrmFornecedorespedidoselecao)
    .controller('CrmFornecedorespedidoselecaoListController', CrmFornecedorespedidoselecaoListController)
    .constant('OPTIONS_CrmFornecedorespedidoselecao', OPTIONS_CrmFornecedorespedidoselecao)
    .constant('MAXOCCURS_CrmFornecedorespedidoselecao', MAXOCCURS_CrmFornecedorespedidoselecao)
    .constant('SELECTS_CrmFornecedorespedidoselecao', SELECTS_CrmFornecedorespedidoselecao)
    .constant('FIELDS_CrmFornecedorespedidoselecao', FIELDS_CrmFornecedorespedidoselecao)
    .run(['$rootScope', 'FIELDS_CrmFornecedorespedidoselecao', 'OPTIONS_CrmFornecedorespedidoselecao', 'MAXOCCURS_CrmFornecedorespedidoselecao', 'SELECTS_CrmFornecedorespedidoselecao',
        ($rootScope: any, FIELDS_CrmFornecedorespedidoselecao: object, OPTIONS_CrmFornecedorespedidoselecao: object, MAXOCCURS_CrmFornecedorespedidoselecao: object, SELECTS_CrmFornecedorespedidoselecao: object) => {
            $rootScope.OPTIONS_CrmFornecedorespedidoselecao = OPTIONS_CrmFornecedorespedidoselecao;
            $rootScope.MAXOCCURS_CrmFornecedorespedidoselecao = MAXOCCURS_CrmFornecedorespedidoselecao;
            $rootScope.SELECTS_CrmFornecedorespedidoselecao = SELECTS_CrmFornecedorespedidoselecao;
            $rootScope.FIELDS_CrmFornecedorespedidoselecao = FIELDS_CrmFornecedorespedidoselecao;
        }])
    .config(FornecedorespedidoselecaoRoutes)
    .name
