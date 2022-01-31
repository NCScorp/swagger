import angular = require("angular");
import { CommonsUtilsService } from "../../../../Commons/utils/utils.service";

export class WizardPassocincoController {
  static $inject = ["$scope", "CommonsUtilsService"];
  public form: any;
  public opcoes: any;
  public entity;

  public fieldsConfig = [
    {
      value: "nome",
      label: "Nome do formulário",
      type: "string",
      style: "title",
    },
  ];

  public responsavelCopy: any;

  constructor(
    public $scope: angular.IScope,
    public CommonsUtilsService: CommonsUtilsService
  ) {
    //Inicializações no contrutor
  }

  $onInit() {
    this.responsavelCopy = this.entity.responsavel_conta_nasajon;
    this.formataDatas(this.entity);

    this.opcoes.validarFormulario = () => {
      return this.validarForm();
    };
  }

  formataDatas(entity: any) {
    //Se a data for um objeto do tipo Date, preciso converter pra datestring para formatar
    if (
      this.entity.datainicio != null &&
      typeof this.entity.datainicio == "object"
    ) {
      this.entity.datainicioformatada =
        this.CommonsUtilsService.getDataFormatada(
          this.entity.datainicio.toDateString()
        );
    } else if (
      this.entity.datainicio != null &&
      typeof this.entity.datainicio == "string"
    ) {
      this.entity.datainicioformatada =
        this.CommonsUtilsService.getDataFormatada(this.entity.datainicio);
    }

    //Se datafim tiver sido informada, formato a data para o formato adequado
    if (
      this.entity.datafim != null &&
      this.entity.datafim != "" &&
      typeof this.entity.datafim == "string"
    ) {
      this.entity.datafimformatada = this.CommonsUtilsService.getDataFormatada(
        this.entity.datafim
      );
    }
    //Se datafim não tiver sido informada e datafimformatada já tiver um valor atribuído, limpo datafimformatada
    else if (
      this.entity.datafim == "" &&
      this.entity.datafimformatada != null &&
      this.entity.datafimformatada != ""
    ) {
      this.entity.datafimformatada = "";
    }
  }

  validarForm() {
    return true;
  }
}
