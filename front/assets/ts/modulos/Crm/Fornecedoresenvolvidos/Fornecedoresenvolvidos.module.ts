import angular from "angular";
import { crmFornecedoresenvolvidosDefaultShow, crmFornecedoresenvolvidosDefault } from "./components";
import { CrmFornecedoresenvolvidosDefaultController } from "./default.form";
import { CrmFornecedoresenvolvidosDefaultShowController } from "./default.form.show";
import { CrmFornecedoresenvolvidos } from "./factory";
import { CrmFornecedoresenvolvidosFormModalController, CrmFornecedoresenvolvidosFormService } from "./modal";
import { CrmFornecedoresenvolvidosFormModalShowController, CrmFornecedoresenvolvidosFormShowService } from "./modal.show";

export const FornecedoresenvolvidosModule = angular
    .module('FornecedoresenvolvidosModule', [])
    .controller('CrmFornecedoresenvolvidosDefaultController', CrmFornecedoresenvolvidosDefaultController)
    .service('CrmFornecedoresenvolvidos', CrmFornecedoresenvolvidos)
    .component('crmFornecedoresenvolvidosDefaultShow', crmFornecedoresenvolvidosDefaultShow)
    .component('crmFornecedoresenvolvidosDefault', crmFornecedoresenvolvidosDefault)
    .controller('CrmFornecedoresenvolvidosDefaultShowController', CrmFornecedoresenvolvidosDefaultShowController)
    .controller('CrmFornecedoresenvolvidosFormModalShowController', CrmFornecedoresenvolvidosFormModalShowController)
    .service('CrmFornecedoresenvolvidosFormShowService', CrmFornecedoresenvolvidosFormShowService)
    .controller('CrmFornecedoresenvolvidosFormModalController', CrmFornecedoresenvolvidosFormModalController)
    .service('CrmFornecedoresenvolvidosFormService', CrmFornecedoresenvolvidosFormService)
    .name