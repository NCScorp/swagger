import angular from "angular";
import { ProjetosShowFormFullController } from "./projetos-showform-full.controller";

export class ProjetosShowFormFullComponent implements angular.IComponentOptions {
  static selector = 'projetosShowFormFullComponent';
  static controller = ProjetosShowFormFullController;
  static template = require('./projetos-showform-full.html');
  static bindings = {
    entity: '=',
    camposCustomizados: '<',
    camposCustomizadosConfig: '<'
  }
}
