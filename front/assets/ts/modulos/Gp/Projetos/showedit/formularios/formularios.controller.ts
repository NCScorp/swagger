import { FormularioModalService } from "./formulariomodal/formulario.modal.service";
import { IFormulario, IFormularioModulo, IFormularioRespostas } from "./formularios.interface";
import { FormulariosService } from "../../formularios.service";
import { FormulariosModulosService } from "../../formularios-modulos.service";
export class FormulariosController {
  static $inject = ['$scope', 'formularioModalService', 'formulariosModulosService', '$rootScope'];

  public arrQtdSekeletonsFormLin: number[] = [0, 1];
  public formulariosEntities: IFormulario[];
  public formulariosRespostas: IFormularioRespostas[];
  public busyFormulario: boolean;

  constructor(public $scope: ng.IScope, public formularioModalService: FormularioModalService, public formulariosModulosService: FormulariosModulosService, public $rootScope: ng.IRootScopeService) {
    $scope.$watchGroup(['$ctrl.formulariosEntities', '$ctrl.formulariosRespostas'], () => {
      if (this.formulariosEntities && this.formulariosRespostas) {
        this.contaRespostasFormulario();
      }
    });
  }

  private contaRespostasFormulario() {
    this.formulariosEntities.forEach((formulario, index) => {
      this.busyFormulario = true;

      formulario.countQuestoes = 0;
      formulario.countRespostas = 0;
      this.formulariosRespostas.forEach((formularioResposta) => {
        if (formulario.formulariomodulo === formularioResposta.formulariomodulo && formularioResposta.formularioitem.tipo !== 1) {
          ++formulario.countQuestoes;
          if (formularioResposta.resposta) {
            ++formulario.countRespostas;
          }
        }
      });

      this.busyFormulario = false;
    });
  }

  openFormularioModal(formulario: IFormulario) {
    this.formulariosModulosService.get({ id: formulario.formulariomodulo }).then((formularioModulo: IFormularioModulo) => {
      let respostas = this.formulariosRespostas.filter((formularioRespostas) => formularioRespostas.formularioitem.formulario === formulario.formulario);
      let modal = this.formularioModalService.open(formularioModulo, respostas);

      modal.result.then((alterouFormulario: boolean) => {
        if (alterouFormulario) {
          this.$rootScope.$broadcast('projetos_alterou_formulario');
        }
      });
      this.$scope.$on('formularios_modal_closed', (event, alterouFormulario: boolean) => {
        if (alterouFormulario) {
          this.$rootScope.$broadcast('projetos_alterou_formulario');
        }
      })
    })
  }
}