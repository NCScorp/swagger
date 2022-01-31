import angular = require("angular");
import { WizardPassocincoController } from "./wizard.passocinco.controller";

export class wizardPassocinco implements angular.IComponentOptions {
  static selector = "wizardPassocinco";
  static template = require("./wizard.passocinco.html");
  static controller = WizardPassocincoController;
  static bindings = {
    entity: "=",
    opcoes: "=",
  };
}
