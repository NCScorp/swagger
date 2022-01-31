import angular from "angular";
import { FormulariosController } from "./formularios.controller";

export class FormulariosComponent implements angular.IComponentOptions {
  static selector = 'formulariosComponent';
  static controller = FormulariosController;
  static template = require('./formularios.html');
  static bindings = {
    entity: '<',
    formulariosEntities: '<',
    formulariosRespostas: '<',
    busyFormulario: '='
  }
}
