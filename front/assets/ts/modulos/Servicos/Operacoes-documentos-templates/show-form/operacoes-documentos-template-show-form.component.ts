import {OperacoesDocumentosTemplatesFormController} from "../form/operacoes-documentos-templates-form.controller";
import {OperacoesDocumentosTemplatesShowFormController} from "./operacoes-documentos-template-show-form.controller";

export class OperacoesDocumentosTemplatesShowFormComponent implements angular.IComponentOptions {
    static selector = 'operacoesDocumentosTemplatesShowForm';
    static controller = OperacoesDocumentosTemplatesShowFormController;
    static template = require('./operacoes-documentos-templates-show-form.html');
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