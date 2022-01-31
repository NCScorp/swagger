import angular from "angular";
import { CrmPropostasitensfamiliasListController } from ".";
import { crmPropostasitensfamiliasDefaultShow, crmPropostasitensfamiliasDefault, crmPropostasitensfamiliasEdicao } from "./components";
import { PropostasitensfamiliasRoutes } from "./config";
import { FIELDS_CrmPropostasitensfamilias } from "./constant";
import { CrmPropostasitensfamiliasDefaultController } from "./default.form";
import { CrmPropostasitensfamiliasDefaultShowController } from "./default.form.show";
import { CrmPropostasitensfamiliasEdicaoController } from "./edicao.form";
import { CrmPropostasitensfamiliasFormController } from "./edit";
import { CrmPropostasitensfamilias } from "./factory";
import { CrmPropostasitensfamiliasFormModalController, CrmPropostasitensfamiliasFormService } from "./modal";
import { CrmPropostasitensfamiliasFormNewController } from "./new";
import { CrmPropostasitensfamiliasFormShowController } from "./show";
import { ModalExclusaoPropItemFamiliaController, ModalExclusaoPropItemFamiliaService } from "./modal-exclusao";

export const PropostasitensfamiliasModule = angular
    .module('PropostasitensfamiliasModule', [])
    .service('modalExclusaoPropItemFamiliaService', ModalExclusaoPropItemFamiliaService)
    .controller('ModalExclusaoPropItemFamiliaController', ModalExclusaoPropItemFamiliaController)
    .controller('CrmPropostasitensfamiliasFormShowController', CrmPropostasitensfamiliasFormShowController)
    .controller('CrmPropostasitensfamiliasFormController', CrmPropostasitensfamiliasFormController)
    .controller('CrmPropostasitensfamiliasDefaultShowController', CrmPropostasitensfamiliasDefaultShowController)
    .controller('CrmPropostasitensfamiliasDefaultController', CrmPropostasitensfamiliasDefaultController)
    .controller('CrmPropostasitensfamiliasEdicaoController', CrmPropostasitensfamiliasEdicaoController)
    .service('CrmPropostasitensfamilias', CrmPropostasitensfamilias)
    .controller('CrmPropostasitensfamiliasListController', CrmPropostasitensfamiliasListController)
    .controller('CrmPropostasitensfamiliasFormNewController', CrmPropostasitensfamiliasFormNewController)
    .controller('CrmPropostasitensfamiliasFormModalController', CrmPropostasitensfamiliasFormModalController)
    .service('CrmPropostasitensfamiliasFormService', CrmPropostasitensfamiliasFormService)
    .component('crmPropostasitensfamiliasDefaultShow', crmPropostasitensfamiliasDefaultShow)
    .component('crmPropostasitensfamiliasDefault', crmPropostasitensfamiliasDefault)
    .component('crmPropostasitensfamiliasEdicao', crmPropostasitensfamiliasEdicao)
    .constant('FIELDS_CrmPropostasitensfamilias', FIELDS_CrmPropostasitensfamilias
    )
    .run(['$rootScope', 'FIELDS_CrmPropostasitensfamilias',
        ($rootScope: any, FIELDS_CrmPropostasitensfamilias: object) => {
            $rootScope.FIELDS_CrmPropostasitensfamilias = FIELDS_CrmPropostasitensfamilias;
        }])
    .config(PropostasitensfamiliasRoutes)
    .name