import angular from "angular";
import { EscopoController } from "./escopo.controller";

export class EscopoComponent implements angular.IComponentOptions {
  static selector = 'escopoComponent';
  static controller = EscopoController;
  static template = require('./escopo.html');
  static bindings = {
    entity: '<',
    escopoEntities: '<'
  }
}
