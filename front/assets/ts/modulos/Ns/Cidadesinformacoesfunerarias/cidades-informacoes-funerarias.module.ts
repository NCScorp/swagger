import angular from "angular";
import { NsCidadesinformacoesfunerariasListController } from ".";
import { NsCidadesinformacoesfunerariasCadastroController } from "./cadastro.form";
import { nsCidadesinformacoesfunerariasViewShow, nsCidadesinformacoesfunerariasCadastro } from "./components";
import { CidadesinformacoesfunerariasRoutes } from "./config";
import { FIELDS_NsCidadesinformacoesfunerarias } from "./constant";
import { NsCidadesinformacoesfunerariasFormController } from "./edit";
import { NsCidadesinformacoesfunerarias } from "./factory";
import { CrmCidadesInfoFunerariasPretadoresModalController, CrmCidadesInfoFunerariasPretadoresModalService } from "./modal-prestadores";
import { NsCidadesinformacoesfunerariasFormNewController } from "./new";
import { NsCidadesinformacoesfunerariasFormShowController } from "./show";
import { NsCidadesinformacoesfunerariasViewShowController } from "./view.form.show";

export const CidadesinformacoesfunerariasModule = angular
    .module('CidadesinformacoesfunerariasModule', [])
    .controller('NsCidadesinformacoesfunerariasFormController', NsCidadesinformacoesfunerariasFormController)
    .service('NsCidadesinformacoesfunerarias', NsCidadesinformacoesfunerarias)
    .controller('NsCidadesinformacoesfunerariasListController', NsCidadesinformacoesfunerariasListController)
    .controller('CrmCidadesInfoFunerariasPretadoresModalController', CrmCidadesInfoFunerariasPretadoresModalController)
    .controller('NsCidadesinformacoesfunerariasFormNewController', NsCidadesinformacoesfunerariasFormNewController)
    .controller('NsCidadesinformacoesfunerariasFormShowController', NsCidadesinformacoesfunerariasFormShowController)
    .controller('NsCidadesinformacoesfunerariasViewShowController', NsCidadesinformacoesfunerariasViewShowController)
    .service('CrmCidadesInfoFunerariasPretadoresModalService', CrmCidadesInfoFunerariasPretadoresModalService)
    .controller('NsCidadesinformacoesfunerariasCadastroController', NsCidadesinformacoesfunerariasCadastroController)
    .component('nsCidadesinformacoesfunerariasViewShow', nsCidadesinformacoesfunerariasViewShow)
    .component('nsCidadesinformacoesfunerariasCadastro', nsCidadesinformacoesfunerariasCadastro)
    .constant('OPTIONS_NsCidadesinformacoesfunerarias', { 'municipio': 'Município', })
    .constant('MAXOCCURS_NsCidadesinformacoesfunerarias', {})
    .constant('SELECTS_NsCidadesinformacoesfunerarias', {})
    .constant('FIELDS_NsCidadesinformacoesfunerarias', FIELDS_NsCidadesinformacoesfunerarias)
    .run(['$rootScope', 'FIELDS_NsCidadesinformacoesfunerarias', 'OPTIONS_NsCidadesinformacoesfunerarias', 'MAXOCCURS_NsCidadesinformacoesfunerarias', 'SELECTS_NsCidadesinformacoesfunerarias',
        ($rootScope: any, FIELDS_NsCidadesinformacoesfunerarias: object, OPTIONS_NsCidadesinformacoesfunerarias: object, MAXOCCURS_NsCidadesinformacoesfunerarias: object, SELECTS_NsCidadesinformacoesfunerarias: object) => {
            $rootScope.OPTIONS_NsCidadesinformacoesfunerarias = OPTIONS_NsCidadesinformacoesfunerarias;
            $rootScope.MAXOCCURS_NsCidadesinformacoesfunerarias = MAXOCCURS_NsCidadesinformacoesfunerarias;
            $rootScope.SELECTS_NsCidadesinformacoesfunerarias = SELECTS_NsCidadesinformacoesfunerarias;
            $rootScope.FIELDS_NsCidadesinformacoesfunerarias = FIELDS_NsCidadesinformacoesfunerarias;
        }])
    .config(CidadesinformacoesfunerariasRoutes)
    .name