import angular = require("angular");
import { WizardPassodoisController } from "./wizard.passodois.controller";

export class wizardPassodois implements angular.IComponentOptions {
  static selector = "wizardPassodois";
  static template = require("./wizard.passodois.html");
  static controller = WizardPassodoisController;
  static bindings = {
    entity: "=",
    opcoes: "=",
  };
}
