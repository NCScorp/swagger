import angular from "angular";
import { crmTemplatespropostascapituloscomposicoesDefault } from "./components";
import { CrmTemplatespropostascapituloscomposicoesDefaultController } from "./default.form";
import { CrmTemplatespropostascapituloscomposicoes } from "./factory";
import { CrmTemplatespropostasCapitulosComposicoesFormModalController, CrmTemplatespropostascapitulosComposicoesFormService } from "./modal";

export const TemplatespropostascapituloscomposicoesModule = angular
    .module('TemplatespropostascapituloscomposicoesModule', [])
    .component('crmTemplatespropostascapituloscomposicoesDefault', crmTemplatespropostascapituloscomposicoesDefault)
    .controller('CrmTemplatespropostascapituloscomposicoesDefaultController', CrmTemplatespropostascapituloscomposicoesDefaultController)
    .service('CrmTemplatespropostascapituloscomposicoes', CrmTemplatespropostascapituloscomposicoes)
    .controller('CrmTemplatespropostasCapitulosComposicoesFormModalController', CrmTemplatespropostasCapitulosComposicoesFormModalController)
    .service('CrmTemplatespropostascapitulosComposicoesFormService', CrmTemplatespropostascapitulosComposicoesFormService)
    .name