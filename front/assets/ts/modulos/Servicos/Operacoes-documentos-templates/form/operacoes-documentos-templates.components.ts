import angular = require('angular');

import {OperacoesDocumentosTemplatesFormController} from "./operacoes-documentos-templates-form.controller";

export class OperacoesDocumentosTemplatesFormComponent implements angular.IComponentOptions {
    static selector = 'operacoesDocumentosTemplatesForm';
    static controller = OperacoesDocumentosTemplatesFormController;
    static template = require('./operacoes-documentos-templates-form.html');
    static transclude = true;
    static bindings = {
        entity: '<',
        form: '<',
        busy: '=',
        action: '<',
        constructors: '<',
        collection: '<'
    }
}