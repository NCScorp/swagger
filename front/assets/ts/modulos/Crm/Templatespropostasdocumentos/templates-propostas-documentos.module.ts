import angular from "angular";
import { crmTemplatespropostasdocumentosDefaultShow, crmTemplatespropostasdocumentosDefault } from "./components";
import { CrmTemplatespropostasdocumentosDefaultController } from "./default.form";
import { CrmTemplatespropostasdocumentosDefaultShowController } from "./default.form.show";
import { CrmTemplatespropostasdocumentos } from "./factory";
import { CrmTemplatespropostasdocumentosFormModalController, CrmTemplatespropostasdocumentosFormService } from "./modal";
import { CrmTemplatespropostasdocumentosFormModalShowController, CrmTemplatespropostasdocumentosFormShowService } from "./modal.show";

export const TemplatespropostasdocumentosModule = angular
    .module('TemplatespropostasdocumentosModule', [])
    .component('crmTemplatespropostasdocumentosDefaultShow', crmTemplatespropostasdocumentosDefaultShow)
    .component('crmTemplatespropostasdocumentosDefault', crmTemplatespropostasdocumentosDefault)
    .controller('CrmTemplatespropostasdocumentosDefaultShowController', CrmTemplatespropostasdocumentosDefaultShowController)
    .controller('CrmTemplatespropostasdocumentosDefaultController', CrmTemplatespropostasdocumentosDefaultController)
    .service('CrmTemplatespropostasdocumentos', CrmTemplatespropostasdocumentos)
    .controller('CrmTemplatespropostasdocumentosFormModalShowController', CrmTemplatespropostasdocumentosFormModalShowController)
    .service('CrmTemplatespropostasdocumentosFormShowService', CrmTemplatespropostasdocumentosFormShowService)
    .controller('CrmTemplatespropostasdocumentosFormModalController', CrmTemplatespropostasdocumentosFormModalController)
    .service('CrmTemplatespropostasdocumentosFormService', CrmTemplatespropostasdocumentosFormService)
    .name