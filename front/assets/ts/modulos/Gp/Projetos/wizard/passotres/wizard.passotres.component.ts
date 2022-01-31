import angular = require("angular");
import { WizardPassotresController } from "./wizard.passotres.controller";

export class wizardPassotres implements angular.IComponentOptions {
  static selector = "wizardPassotres";
  static template = require("./wizard.passotres.html");
  static controller = WizardPassotresController;
  static bindings = {
    entity: "=",
    opcoes: "=",
  };
}
