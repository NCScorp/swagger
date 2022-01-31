import angular from "angular";
import { NsFornecedoresListController } from ".";
import { nsFornecedoresShowShow, nsFornecedoresDefault, nsFornecedoresSuspender, nsFornecedoresFornecedorreativar, nsFornecedoresFornecedoradvertir } from "./components";
import { FornecedoresRoutes } from "./config";
import { FIELDS_NsFornecedores } from "./constant";
import { NsFornecedoresDefaultController } from "./default.form";
import { NsFornecedoresFormController } from "./edit";
import { NsFornecedores } from "./factory";
import { NsFornecedoresFornecedoradvertirController } from "./fornecedoradvertir.form";
import { NsFornecedoresFormFornecedoradvertirController, NsFornecedoresFormFornecedoradvertirService } from "./fornecedoradvertir.modal";
import { NsFornecedoresFornecedorreativarController } from "./fornecedorreativar.form";
import { NsFornecedoresFormFornecedorreativarController, NsFornecedoresFormFornecedorreativarService } from "./fornecedorreativar.modal";
import { NsFornecedoresFormNewController } from "./new";
import { NsFornecedoresFormShowController } from "./show";
import { NsFornecedoresShowShowControllerSobrescrito } from "./show.default";
import { NsFornecedoresShowShowController } from "./show.form.show";
import { NsFornecedoresSuspenderController } from "./suspender.form";
import { NsFornecedoresFormSuspenderController, NsFornecedoresFormSuspenderService } from "./suspender.modal";
import { FornecedoresUploadImagemModule } from "./uploadimagem/fornecedores-upload-imagem.module";
import { NsFornecedoresErp } from "./factory-erp";

export const FornecedoresModule = angular
    .module('FornecedoresModule', [FornecedoresUploadImagemModule])
    .controller('NsFornecedoresDefaultController', NsFornecedoresDefaultController)
    .controller('NsFornecedoresFormController', NsFornecedoresFormController)
    .service('NsFornecedores', NsFornecedores)
    .service('NsFornecedoresErp', NsFornecedoresErp)
    .controller('NsFornecedoresFornecedoradvertirController', NsFornecedoresFornecedoradvertirController)
    .controller('NsFornecedoresFormFornecedoradvertirController', NsFornecedoresFormFornecedoradvertirController)
    .controller('NsFornecedoresFornecedorreativarController', NsFornecedoresFornecedorreativarController)
    .controller('NsFornecedoresFormFornecedorreativarController', NsFornecedoresFormFornecedorreativarController)
    .controller('NsFornecedoresListController', NsFornecedoresListController)
    .controller('NsFornecedoresFormNewController', NsFornecedoresFormNewController)
    .controller('NsFornecedoresShowShowController', NsFornecedoresShowShowControllerSobrescrito)
    .controller('NsFornecedoresShowShowController', NsFornecedoresShowShowController)
    .controller('NsFornecedoresFormShowController', NsFornecedoresFormShowController)
    .controller('NsFornecedoresSuspenderController', NsFornecedoresSuspenderController)
    .controller('NsFornecedoresFormSuspenderController', NsFornecedoresFormSuspenderController)
    .service('NsFornecedoresFormSuspenderService', NsFornecedoresFormSuspenderService)
    .service('NsFornecedoresFormFornecedorreativarService', NsFornecedoresFormFornecedorreativarService)
    .service('NsFornecedoresFormFornecedoradvertirService', NsFornecedoresFormFornecedoradvertirService)
    .component('nsFornecedoresShowShow', nsFornecedoresShowShow)
    .component('nsFornecedoresDefault', nsFornecedoresDefault)
    .component('nsFornecedoresSuspender', nsFornecedoresSuspender)
    .component('nsFornecedoresFornecedorreativar', nsFornecedoresFornecedorreativar)
    .component('nsFornecedoresFornecedoradvertir', nsFornecedoresFornecedoradvertir)
    .constant('FIELDS_NsFornecedores', FIELDS_NsFornecedores)
    .constant('OPTIONS_NsFornecedores', { 'municipionome': 'Município', })
    .constant('MAXOCCURS_NsFornecedores', { 'status': 1, })
    .constant('SELECTS_NsFornecedores', {})
    .constant('SELECT_Status_NsFornecedores', {
        '0': 'Ativa',
        '1': 'Suspensa',
        '2': 'Banida',
    })
    .run(['$rootScope', 'FIELDS_NsFornecedores', 'OPTIONS_NsFornecedores', 'MAXOCCURS_NsFornecedores', 'SELECTS_NsFornecedores', 'SELECT_Status_NsFornecedores',
        ($rootScope: any, FIELDS_NsFornecedores: object, OPTIONS_NsFornecedores: object, MAXOCCURS_NsFornecedores: object, SELECTS_NsFornecedores: object, SELECT_Status_NsFornecedores: object) => {
            $rootScope.OPTIONS_NsFornecedores = OPTIONS_NsFornecedores;
            $rootScope.MAXOCCURS_NsFornecedores = MAXOCCURS_NsFornecedores;
            $rootScope.SELECTS_NsFornecedores = SELECTS_NsFornecedores;
            $rootScope.SELECT_Status_NsFornecedores = SELECT_Status_NsFornecedores; $rootScope.FIELDS_NsFornecedores = FIELDS_NsFornecedores;
        }])
    .config(FornecedoresRoutes)
    .name