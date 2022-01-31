import angular from "angular";
import { NsDocumentosfopListController } from ".";
import { DocumentosfopRoutes } from "./config";
import { FIELDS_NsDocumentosfop } from "./constant";
import { NsDocumentosfop } from "./factory";

export const DocumentosfopModule = angular
    .module('DocumentosfopModule', [])
    .service('NsDocumentosfop', NsDocumentosfop)
    .controller('NsDocumentosfopListController', NsDocumentosfopListController)
    .constant('FIELDS_NsDocumentosfop', FIELDS_NsDocumentosfop)
    .run(['$rootScope', 'FIELDS_NsDocumentosfop',
        ($rootScope: any, FIELDS_NsDocumentosfop: object) => {
            $rootScope.FIELDS_NsDocumentosfop = FIELDS_NsDocumentosfop;
        }])
    .config(DocumentosfopRoutes)
    .name