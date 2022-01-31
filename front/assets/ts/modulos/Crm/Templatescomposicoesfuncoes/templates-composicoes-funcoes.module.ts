import angular from "angular";
import { crmTemplatescomposicoesfuncoesDefault } from "./components";
import { CrmTemplatescomposicoesfuncoesDefaultController } from "./default.form";
import { CrmTemplatescomposicoesfuncoes } from "./factory";
import { CrmTemplatescomposicoesfuncoesFormModalController, CrmTemplatescomposicoesfuncoesFormService } from "./modal";

export const TemplatescomposicoesfuncoesModule = angular
    .module('TemplatescomposicoesfuncoesModule', [])
    .component('crmTemplatescomposicoesfuncoesDefault', crmTemplatescomposicoesfuncoesDefault)
    .controller('CrmTemplatescomposicoesfuncoesDefaultController', CrmTemplatescomposicoesfuncoesDefaultController)
    .service('CrmTemplatescomposicoesfuncoes', CrmTemplatescomposicoesfuncoes)
    .controller('CrmTemplatescomposicoesfuncoesFormModalController', CrmTemplatescomposicoesfuncoesFormModalController)
    .service('CrmTemplatescomposicoesfuncoesFormService', CrmTemplatescomposicoesfuncoesFormService)
    .name