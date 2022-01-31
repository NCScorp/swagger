import angular = require("angular");
import { FormulariosService } from "../../formularios.service";

export class WizardPassotresController {
  static $inject = ["$scope", "formulariosService"];
  public form: any;
  public opcoes: any;
  public entity;
  public formulario;

  public fieldsConfig = [
    {
      value: "nome",
      label: "Nome do formulário",
      type: "string",
      style: "title",
    },
  ];

  constructor(
    public $scope: angular.IScope,
    public formulariosService: FormulariosService
  ) {
    //Inicializações no contrutor
    $scope.$watch("$ctrl.formulario", (newValue) => {
      this.addFormulario(newValue);
    });

    $scope.$watch("$ctrl.entity", (newValue: any) => {
      if (!newValue.formularioList) {
        this.entity.formularioList = [];
      }
    });
  }

  $onInit() {
    this.opcoes.validarFormulario = () => {
      return this.validarForm();
    };
  }

  validarForm() {
    return true;
  }

  addFormulario(formulario) {
    if (formulario) {
      if (this.entity.formularioList.length > 0) {
        const formularioIndex = this.entity.formularioList.findIndex(
          (formularioItem) => {
            return formularioItem.formulario == formulario.formulario;
          }
        );

        if (formularioIndex == -1) {
          this.entity.formularioList.push(formulario);
        }
      } else {
        this.entity.formularioList.push(formulario);
      }
    }
  }

  removeFormulario(formulario) {
    this.entity.formularioList = this.entity.formularioList.filter(
      (formularioItem) => formularioItem.formulario != formulario.formulario
    );
  }
}
