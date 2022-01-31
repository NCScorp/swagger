import angular from "angular";

import { ProjetosEditFormController } from "./projetos-editform.controller";

export class ProjetosEditFormComponent implements angular.IComponentOptions {
  static selector = 'projetosEditFormComponent';
  static controller = ProjetosEditFormController;
  static template = require('./projetos-editform.html');
  static bindings = {
    originalEntity: '=',
    form: '<',
    camposCustomizados: '<',
    camposCustomizadosConfig: '<',
    editMode: '='
  }
}
