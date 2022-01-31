import angular = require("angular");
import { WizardPassoquatroController } from "./wizard.passoquatro.controller";

export class wizardPassoquatro implements angular.IComponentOptions {
  static selector = "wizardPassoquatro";
  static template = require("./wizard.passoquatro.html");
  static controller = WizardPassoquatroController;
  static bindings = {
    entity: "=",
    opcoes: "=",
  };
}
