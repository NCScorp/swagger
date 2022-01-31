import angular = require("angular");

export class WizardPassoquatroController {
  public form: any;
  public opcoes: any;

  constructor() {
    //Inicializações no contrutor
  }

  $onInit() {
    this.opcoes.validarFormulario = () => {
      return this.validarForm();
    };
  }

  validarForm() {
    return true;
  }
}
