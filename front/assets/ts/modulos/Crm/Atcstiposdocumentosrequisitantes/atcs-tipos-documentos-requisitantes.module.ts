import angular from "angular";
import { CrmAtcstiposdocumentosrequisitantesDefault } from "./components";
import { CrmAtcstiposdocumentosrequisitantesDefaultController } from "./default.form";
import { CrmAtcstiposdocumentosrequisitantes } from "./factory";
import { CrmAtcstiposdocumentosrequisitantesFormModalController, CrmAtcstiposdocumentosrequisitantesFormService } from "./modal";

export const AtcstiposdocumentosrequisitantesModule = angular
    .module('AtcstiposdocumentosrequisitantesModule', [])
    .component('crmAtcstiposdocumentosrequisitantesDefault', CrmAtcstiposdocumentosrequisitantesDefault)
    .controller('CrmAtcstiposdocumentosrequisitantesDefaultController', CrmAtcstiposdocumentosrequisitantesDefaultController)
    .service('CrmAtcstiposdocumentosrequisitantes', CrmAtcstiposdocumentosrequisitantes)
    .controller('CrmAtcstiposdocumentosrequisitantesFormModalController', CrmAtcstiposdocumentosrequisitantesFormModalController)
    .service('CrmAtcstiposdocumentosrequisitantesFormService', CrmAtcstiposdocumentosrequisitantesFormService)
    .name