import angular from "angular";
import { CrmNegociosListController } from ".";
import { crmNegociosDefaultShow, crmNegociosDefault, crmNegociosQualificacao, crmNegociosDesqualificacao } from "./components";
import { NegociosRoutes } from "./config";
import { FIELDS_CrmNegocios } from "./constant";
import { CrmNegociosDefaultController } from "./default.form";
import { CrmNegociosDefaultShowController } from "./default.form.show";
import { CrmNegociosFormDefaultController, CrmNegociosFormDefaultService } from "./delete.modal";
import { CrmNegociosDesqualificacaoController } from "./desqualificacao.form";
import { CrmNegociosFormController } from "./edit";
import { CrmNegocios } from "./factory";
import { NsFollowupsnegociosController } from "./followupsnegocios";
import { CrmNegociosFullController } from "./negocios.full";
import { CrmNegociosFormNewController } from "./new";
import { CrmNegociosFormDesqualificacaoController, CrmNegociosFormDesqualificacaoService } from "./preNegocioDesqualificar.modal";
import { CrmNegociosFormQualificacaoController, CrmNegociosFormQualificacaoService } from "./preNegocioQualificar.modal";
import { CrmNegociosQualificacaoController } from "./qualificacao.form";
import { CrmNegociosFormShowController } from "./show";

export const NegociosModule = angular
    .module('NegociosModule', [])
    .controller('CrmNegociosDefaultController', CrmNegociosDefaultController)
    .controller('CrmNegociosDefaultShowController', CrmNegociosDefaultShowController)
    .controller('CrmNegociosDesqualificacaoController', CrmNegociosDesqualificacaoController)
    .controller('CrmNegociosFormController', CrmNegociosFormController)
    .service('CrmNegocios', CrmNegocios)
    .controller('NsFollowupsnegociosController', NsFollowupsnegociosController)
    .controller('CrmNegociosFullController', CrmNegociosFullController)
    .controller('CrmNegociosFormNewController', CrmNegociosFormNewController)
    .controller('CrmNegociosFormDesqualificacaoController', CrmNegociosFormDesqualificacaoController)
    .controller('CrmNegociosFormQualificacaoController', CrmNegociosFormQualificacaoController)
    .controller('CrmNegociosFormShowController', CrmNegociosFormShowController)
    .controller('CrmNegociosQualificacaoController', CrmNegociosQualificacaoController)
    .service('CrmNegociosFormQualificacaoService', CrmNegociosFormQualificacaoService)
    .service('CrmNegociosFormDesqualificacaoService', CrmNegociosFormDesqualificacaoService)
    .controller('CrmNegociosListController', CrmNegociosListController)
    .controller('CrmNegociosFormDefaultController', CrmNegociosFormDefaultController)
    .service('CrmNegociosFormDefaultService', CrmNegociosFormDefaultService)
    .component('crmNegociosDefaultShow', crmNegociosDefaultShow)
    .component('crmNegociosDefault', crmNegociosDefault)
    .component('crmNegociosQualificacao', crmNegociosQualificacao)
    .component('crmNegociosDesqualificacao', crmNegociosDesqualificacao)
    .constant('FIELDS_CrmNegocios', FIELDS_CrmNegocios)
    .constant('OPTIONS_CrmNegocios', { 'prenegocio': 'prenegocio', 'tipoqualificacaopn': 'tipoqualificacaopn', })
    .constant('MAXOCCURS_CrmNegocios', {})
    .constant('SELECTS_CrmNegocios', { 'tipoqualificacaopn': { '0': 'Sem qualificação', '1': 'Qualificado', '2': 'Desqualificado', }, })
    .run(['$rootScope', 'FIELDS_CrmNegocios', 'OPTIONS_CrmNegocios', 'MAXOCCURS_CrmNegocios', 'SELECTS_CrmNegocios',
        ($rootScope: any, FIELDS_CrmNegocios: object, OPTIONS_CrmNegocios: object, MAXOCCURS_CrmNegocios: object, SELECTS_CrmNegocios: object) => {
            $rootScope.OPTIONS_CrmNegocios = OPTIONS_CrmNegocios;
            $rootScope.MAXOCCURS_CrmNegocios = MAXOCCURS_CrmNegocios;
            $rootScope.SELECTS_CrmNegocios = SELECTS_CrmNegocios;
            $rootScope.FIELDS_CrmNegocios = FIELDS_CrmNegocios;
        }])
    .config(NegociosRoutes)
    .name
