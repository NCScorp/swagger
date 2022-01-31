import angular from "angular";
import { CrmPropostasitensListController } from ".";
import { crmPropostasitensDefaultShow, crmPropostasitensDefault, crmPropostasitensEdicao, crmPropostasitensPropostasitensvincularfornecedor, crmPropostasitensPropostasitensfornecedorescolhacliente } from "./components";
import { PropostasitensRoutes } from "./config";
import { FIELDS_CrmPropostasitens } from "./constant";
import { CrmPropostasitensDefaultController } from "./default.form";
import { CrmPropostasitensDefaultShowController } from "./default.form.show";
import { CrmPropostasitensEdicaoController } from "./edicao.form";
import { CrmPropostasitensFormController } from "./edit";
import { CrmPropostasitens } from "./factory";
import { CrmPropostasitensFormModalController, CrmPropostasitensFormService } from "./modal";
import { CrmPropostasitensPretadoresModalController, CrmPropostasitensPretadoresModalService } from "./modal-prestadores";
import { CrmPropostasitensFormNewController } from "./new";
import { CrmBuscaPropostaItemService } from "./proposta-item-service";
import { CrmPropostasitensPropostasitensfornecedorescolhaclienteController } from "./propostasitensfornecedorescolhacliente.form";
import { CrmPropostasitensFormPropostasitensfornecedorescolhaclienteController, CrmPropostasitensFormPropostasitensfornecedorescolhaclienteService } from "./propostasitensfornecedorescolhacliente.modal";
import { CrmPropostasitensPropostasitensvincularfornecedorController } from "./propostasItensVincularFornecedor.form";
import { CrmPropostasitensFormPropostasitensvincularfornecedorController, CrmPropostasitensFormPropostasitensvincularfornecedorService } from "./propostasItensVincularFornecedor.modal";
import { CrmPropostasitensFormShowController } from "./show";
import { ModalExclusaoPropItemService, ModalExclusaoPropItemController } from "./modal-exclusao";

export const PropostasitensModule = angular
    .module('PropostasitensModule', [])
    .service('modalExclusaoPropItemService', ModalExclusaoPropItemService)
    .controller('ModalExclusaoPropItemController', ModalExclusaoPropItemController)
    .controller('CrmPropostasitensDefaultShowController', CrmPropostasitensDefaultShowController)
    .controller('CrmPropostasitensDefaultController', CrmPropostasitensDefaultController)
    .controller('CrmPropostasitensEdicaoController', CrmPropostasitensEdicaoController)
    .controller('CrmPropostasitensFormController', CrmPropostasitensFormController)
    .service('CrmPropostasitens', CrmPropostasitens)
    .controller('CrmPropostasitensListController', CrmPropostasitensListController)
    .controller('CrmPropostasitensPretadoresModalController', CrmPropostasitensPretadoresModalController)
    .controller('CrmPropostasitensFormModalController', CrmPropostasitensFormModalController)
    .controller('CrmPropostasitensFormNewController', CrmPropostasitensFormNewController)
    .service('CrmBuscaPropostaItemService', CrmBuscaPropostaItemService)
    .controller('CrmPropostasitensPropostasitensfornecedorescolhaclienteController', CrmPropostasitensPropostasitensfornecedorescolhaclienteController)
    .controller('CrmPropostasitensFormPropostasitensfornecedorescolhaclienteController', CrmPropostasitensFormPropostasitensfornecedorescolhaclienteController)
    .controller('CrmPropostasitensPropostasitensvincularfornecedorController', CrmPropostasitensPropostasitensvincularfornecedorController)
    .controller('CrmPropostasitensFormPropostasitensvincularfornecedorController', CrmPropostasitensFormPropostasitensvincularfornecedorController)
    .controller('CrmPropostasitensFormShowController', CrmPropostasitensFormShowController)
    .service('CrmPropostasitensFormPropostasitensvincularfornecedorService', CrmPropostasitensFormPropostasitensvincularfornecedorService)
    .service('CrmPropostasitensFormPropostasitensfornecedorescolhaclienteService', CrmPropostasitensFormPropostasitensfornecedorescolhaclienteService)
    .service('CrmPropostasitensFormService', CrmPropostasitensFormService)
    .service('CrmPropostasitensPretadoresModalService', CrmPropostasitensPretadoresModalService)
    .component('crmPropostasitensDefaultShow', crmPropostasitensDefaultShow)
    .component('crmPropostasitensDefault', crmPropostasitensDefault)
    .component('crmPropostasitensEdicao', crmPropostasitensEdicao)
    .component('crmPropostasitensPropostasitensvincularfornecedor', crmPropostasitensPropostasitensvincularfornecedor)
    .component('crmPropostasitensPropostasitensfornecedorescolhacliente', crmPropostasitensPropostasitensfornecedorescolhacliente)
    .constant('FIELDS_CrmPropostasitens', FIELDS_CrmPropostasitens)
    .constant('OPTIONS_CrmPropostasitens', { 'propostacapitulo': 'propostacapitulo', 'fornecedor': 'fornecedor', })
    .constant('MAXOCCURS_CrmPropostasitens', {})
    .constant('SELECTS_CrmPropostasitens', {})
    .run(['$rootScope', 'FIELDS_CrmPropostasitens', 'OPTIONS_CrmPropostasitens', 'MAXOCCURS_CrmPropostasitens', 'SELECTS_CrmPropostasitens',
        ($rootScope: any, FIELDS_CrmPropostasitens: object, OPTIONS_CrmPropostasitens: object, MAXOCCURS_CrmPropostasitens: object, SELECTS_CrmPropostasitens: object) => {
            $rootScope.OPTIONS_CrmPropostasitens = OPTIONS_CrmPropostasitens;
            $rootScope.MAXOCCURS_CrmPropostasitens = MAXOCCURS_CrmPropostasitens;
            $rootScope.SELECTS_CrmPropostasitens = SELECTS_CrmPropostasitens;
            $rootScope.FIELDS_CrmPropostasitens = FIELDS_CrmPropostasitens;
        }])
    .config(PropostasitensRoutes)
    .name