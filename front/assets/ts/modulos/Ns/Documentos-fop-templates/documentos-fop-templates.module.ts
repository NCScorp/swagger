import * as angular from "angular";
import {NsDocumentosFopTemplatesRouting} from "./documentos-fop-templates.routes";
import {NsDocumentosFopTemplatesService} from "./documentos-fop-templates.service";
import {NsDocumentosFopTemplatesIndexController} from "./index/documentos-fop-templates-index";

export const DocumentosFopTemplatesModule = angular.module('DocumentosFopTemplatesModule', ['ui.router.state'])
    .controller('nsDocumentosFopTemplatesIndexController', NsDocumentosFopTemplatesIndexController)
    .service('nsDocumentosFopTemplatesService', NsDocumentosFopTemplatesService)
    .constant('FIELDS_NsDocumentosfoptemplates', [
        {
            value: 'codigoxsl',
            label: 'Código',
            type: 'string',
            style: 'title',
            copy: '',
        },

        {
            value: 'nomexsl',
            label: 'Nome',
            type: 'string',
            style: 'default',
            copy: '',
        },

    ])
    .run(['$rootScope', 'FIELDS_NsDocumentosfoptemplates',
        ($rootScope: any, FIELDS_NsDocumentosfoptemplates: object) => {
            $rootScope.FIELDS_NsDocumentosfoptemplates = FIELDS_NsDocumentosfoptemplates;
        }]
    )
    .config(NsDocumentosFopTemplatesRouting)
    .name;