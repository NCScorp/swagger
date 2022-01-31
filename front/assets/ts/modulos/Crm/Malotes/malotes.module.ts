import angular from "angular";
import { CrmMalotesListController } from ".";
import { CrmMalotesAprovarController } from "./aprovar.form";
import { crmMalotesVisualizacaoShow, crmMalotesDefault, crmMalotesAprovar, crmMalotesEnviar, crmMalotesEditarenvio } from "./components";
import { MalotesRoutes } from "./config";
import { FIELDS_CrmMalotes } from "./constant";
import { CrmMalotesDefaultController } from "./default.form";
import { CrmMalotesFormController } from "./edit";
import { CrmMalotesEditarenvioController } from "./editarenvio.form";
import { CrmMalotesEnviarController } from "./enviar.form";
import { CrmMalotes } from "./factory";
import { CrmMalotesFormAprovarController, CrmMalotesFormAprovarService } from "./maloteAprova.modal";
import { CrmMalotesConfirmMalotecancelaenvioController, CrmMalotesConfirmMalotecancelaenvioService } from "./malotecancelaenvio.confirm";
import { CrmMalotesFormEditarenvioController, CrmMalotesFormEditarenvioService } from "./maloteEditarEnvio.modal";
import { CrmMalotesFormEnviarController, CrmMalotesFormEnviarService } from "./maloteEnviar.modal";
import { MalotesAdicionarDocumentosModalService, MalotesAdicionarDocumentosModalController } from "./modaladddocumentos/modal";
import { CrmMalotesFormNewController } from "./new";
import { CrmMalotesFormShowController } from "./show";
import { CrmMalotesVisualizacaoShowController } from "./visualizacao.form.show";

export const MalotesModule = angular
    .module('MalotesModule', [])

    .service('MalotesAdicionarDocumentosModalService', MalotesAdicionarDocumentosModalService)
    .controller('MalotesAdicionarDocumentosModalController', MalotesAdicionarDocumentosModalController)
    .controller('CrmMalotesDefaultController', CrmMalotesDefaultController)
    .controller('CrmMalotesFormController', CrmMalotesFormController)
    .controller('CrmMalotesEditarenvioController', CrmMalotesEditarenvioController)
    .controller('CrmMalotesEnviarController', CrmMalotesEnviarController)
    .service('CrmMalotes', CrmMalotes)
    .controller('CrmMalotesVisualizacaoShowController', CrmMalotesVisualizacaoShowController)
    .controller('CrmMalotesFormShowController', CrmMalotesFormShowController)
    .controller('CrmMalotesFormNewController', CrmMalotesFormNewController)
    .controller('CrmMalotesFormEnviarController', CrmMalotesFormEnviarController)
    .service('CrmMalotesFormEnviarService', CrmMalotesFormEnviarService)
    .controller('CrmMalotesFormEditarenvioController', CrmMalotesFormEditarenvioController)
    .service('CrmMalotesFormEditarenvioService', CrmMalotesFormEditarenvioService)
    .controller('CrmMalotesListController', CrmMalotesListController)
    .controller('CrmMalotesFormAprovarController', CrmMalotesFormAprovarController)
    .controller('CrmMalotesConfirmMalotecancelaenvioController', CrmMalotesConfirmMalotecancelaenvioController)
    .service('CrmMalotesConfirmMalotecancelaenvioService', CrmMalotesConfirmMalotecancelaenvioService)
    .service('CrmMalotesFormAprovarService', CrmMalotesFormAprovarService)
    .controller('CrmMalotesAprovarController', CrmMalotesAprovarController)
    .component('crmMalotesVisualizacaoShow', crmMalotesVisualizacaoShow)
    .component('crmMalotesDefault', crmMalotesDefault)
    .component('crmMalotesAprovar', crmMalotesAprovar)
    .component('crmMalotesEnviar', crmMalotesEnviar)
    .component('crmMalotesEditarenvio', crmMalotesEditarenvio)
    .constant('FIELDS_CrmMalotes',FIELDS_CrmMalotes)
    .run(['$rootScope', 'FIELDS_CrmMalotes',
        ($rootScope: any, FIELDS_CrmMalotes: object) => {
            $rootScope.FIELDS_CrmMalotes = FIELDS_CrmMalotes;
        }])
    .config(MalotesRoutes)
    .name
