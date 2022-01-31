import angular = require("angular");
import { WizardPassoumController } from "./wizard.passoum.controller";

export class wizardPassoum implements angular.IComponentOptions {
  static selector = "wizardPassoum";
  static template = require("./wizard.passoum.html");
  static controller = WizardPassoumController;
  static bindings = {
    entity: "=",
    opcoes: "=",
  };
}
