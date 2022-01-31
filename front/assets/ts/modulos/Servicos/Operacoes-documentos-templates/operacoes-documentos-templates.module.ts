import angular = require("angular");

import {OperacoesDocumentosTemplatesFormController} from "./form/operacoes-documentos-templates-form.controller";
import {OperacoesDocumentosTemplatesEditController} from "./edit/operacoes-documentos-templates-edit.controller";
import {OperacoesDocumentosTemplatesIndexController} from "./index/operacoes-documentos-templates-index.controller";
import {OperacoesDocumentosTemplatesNewController} from "./new/operacoes-documentos-templates-new.controller";
import {OperacoesDocumentosTemplatesShowController} from "./show/operacoes-documentos-templates-show.controller";
import {OperacoesDocumentosTemplatesShowFormController} from "./show-form/operacoes-documentos-template-show-form.controller";
import {OperacoesDocumentosTemplatesFormComponent} from "./form/operacoes-documentos-templates.components";
import {OperacoesDocumentosTemplatesShowFormComponent} from "./show-form/operacoes-documentos-template-show-form.component";
import {OperacoesDocumentosTemplatesService} from "./operacoes-documentos-templates.service";
import {OperacoesDocumentosTemplatesRouting} from "./operacoes-documentos-templates.routes";

export const OperacoesDocumentosTemplatesModule = angular.module('OperacoesDocumentosTemplatesModule', ['ui.router.state'])
    .controller('operacoesDocumentosTemplatesFormController', OperacoesDocumentosTemplatesFormController)
    .controller('operacoesDocumentosTemplatesEditController', OperacoesDocumentosTemplatesEditController)
    .controller('operacoesDocumentosTemplatesIndexController', OperacoesDocumentosTemplatesIndexController)
    .controller('operacoesDocumentosTemplatesNewController', OperacoesDocumentosTemplatesNewController)
    .controller('operacoesDocumentosTemplatesShowController', OperacoesDocumentosTemplatesShowController)
    .controller('operacoesDocumentosTemplatesShowFormController', OperacoesDocumentosTemplatesShowFormController)
    .component(OperacoesDocumentosTemplatesFormComponent.selector, OperacoesDocumentosTemplatesFormComponent)
    .component(OperacoesDocumentosTemplatesShowFormComponent.selector, OperacoesDocumentosTemplatesShowFormComponent)
    .service('operacoesDocumentosTemplatesService', OperacoesDocumentosTemplatesService)
    .config(OperacoesDocumentosTemplatesRouting)
    .constant('FIELDS_Operacoesdocumentostemplates', [
        {
            value: 'created_by',
            label: 'created_by',
            type: 'date',
            style: 'title',
            copy: '',
        },

    ])
    .run(['$rootScope', 'FIELDS_Operacoesdocumentostemplates',
        ($rootScope: any, FIELDS_Operacoesdocumentostemplates: object) => {
            $rootScope.FIELDS_Operacoesdocumentostemplates = FIELDS_Operacoesdocumentostemplates;
        }
    ])
    .name