import angular from "angular";
import { crmComposicoesfuncoesDefault } from "./components";
import { CrmComposicoesfuncoesDefaultController } from "./default.form";
import { CrmComposicoesfuncoes } from "./factory";
import { CrmComposicoesfuncoesFormModalController, CrmComposicoesfuncoesFormService } from "./modal";

export const ComposicoesfuncoesModule = angular
    .module('ComposicoesfuncoesModule', [])
    .component('crmComposicoesfuncoesDefault', crmComposicoesfuncoesDefault)
    .controller('CrmComposicoesfuncoesDefaultController', CrmComposicoesfuncoesDefaultController)
    .service('CrmComposicoesfuncoes', CrmComposicoesfuncoes)
    .controller('CrmComposicoesfuncoesFormModalController', CrmComposicoesfuncoesFormModalController)
    .service('CrmComposicoesfuncoesFormService', CrmComposicoesfuncoesFormService)
    .name