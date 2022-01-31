import angular from "angular";
import { ProjetosShowFormController } from "./projetos-showform.controller";

export class ProjetosShowFormComponent implements angular.IComponentOptions {
  static selector = 'projetosShowFormComponent';
  static controller = ProjetosShowFormController;
  static template = require('./projetos-showform.html');
  static bindings = {
    entity: '=',
    camposCustomizados: '<',
    camposCustomizadosConfig: '<'
  }
}
