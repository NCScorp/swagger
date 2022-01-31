import angular from "angular";
import { CrmPropostasitensfuncoesListController } from ".";
import { crmPropostasitensfuncoesDefaultShow, crmPropostasitensfuncoesDefault, crmPropostasitensfuncoesEdicao } from "./components";
import { PropostasitensfuncoesRoutes } from "./config";
import { FIELDS_CrmPropostasitensfuncoes } from "./constant";
import { CrmPropostasitensfuncoesDefaultController } from "./default.form";
import { CrmPropostasitensfuncoesDefaultShowController } from "./default.form.show";
import { CrmPropostasitensfuncoesEdicaoController } from "./edicao.form";
import { CrmPropostasitensfuncoesFormController } from "./edit";
import { CrmPropostasitensfuncoes } from "./factory";
import { CrmPropostasitensfuncoesFormModalController, CrmPropostasitensfuncoesFormService } from "./modal";
import { CrmPropostasitensfuncoesFormNewController } from "./new";
import { CrmPropostasitensfuncoesFormShowController } from "./show";
import { ModalExclusaoPropItemFuncaoController, ModalExclusaoPropItemFuncaoService } from "./modal-exclusao";

export const PropostasitensfuncoesModule = angular
    .module('PropostasitensfuncoesModule', [])
    .controller('CrmPropostasitensfuncoesDefaultShowController', CrmPropostasitensfuncoesDefaultShowController)
    .controller('CrmPropostasitensfuncoesDefaultController', CrmPropostasitensfuncoesDefaultController)
    .controller('CrmPropostasitensfuncoesEdicaoController', CrmPropostasitensfuncoesEdicaoController)
    .controller('CrmPropostasitensfuncoesFormController', CrmPropostasitensfuncoesFormController)
    .service('CrmPropostasitensfuncoes', CrmPropostasitensfuncoes)
    .controller('CrmPropostasitensfuncoesListController', CrmPropostasitensfuncoesListController)
    .controller('CrmPropostasitensfuncoesFormModalController', CrmPropostasitensfuncoesFormModalController)
    .controller('CrmPropostasitensfuncoesFormNewController', CrmPropostasitensfuncoesFormNewController)
    .controller('CrmPropostasitensfuncoesFormShowController', CrmPropostasitensfuncoesFormShowController)
    .service('CrmPropostasitensfuncoesFormService', CrmPropostasitensfuncoesFormService)
    .service('modalExclusaoPropItemFuncaoService', ModalExclusaoPropItemFuncaoService)
    .controller('ModalExclusaoPropItemFuncaoController', ModalExclusaoPropItemFuncaoController)
    .component('crmPropostasitensfuncoesDefaultShow', crmPropostasitensfuncoesDefaultShow)
    .component('crmPropostasitensfuncoesDefault', crmPropostasitensfuncoesDefault)
    .component('crmPropostasitensfuncoesEdicao', crmPropostasitensfuncoesEdicao)
    .constant('FIELDS_CrmPropostasitensfuncoes', FIELDS_CrmPropostasitensfuncoes)
    .run(['$rootScope', 'FIELDS_CrmPropostasitensfuncoes',
        ($rootScope: any, FIELDS_CrmPropostasitensfuncoes: object) => {
            $rootScope.FIELDS_CrmPropostasitensfuncoes = FIELDS_CrmPropostasitensfuncoes;
        }])
    .config(PropostasitensfuncoesRoutes)
    .name