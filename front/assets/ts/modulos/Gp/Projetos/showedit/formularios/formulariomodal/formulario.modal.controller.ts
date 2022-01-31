import { FormulariosModulosRespostasService } from '../../../formularios-modulos-respostas.service';
import { FormulariosModulosService } from '../../../formularios-modulos.service';
import { IFormularioItem, IFormularioModulo, IFormularioRespostas } from '../formularios.interface';
import { ETipoItemFormulario } from './enum/tipoItemFormulario.enum';
import { IFormEntity } from './models/formconfig.interface';

export class FormularioModalController implements ng.IController {
  static $inject = ['formularioModulo', 'respostas', '$scope', '$uibModalInstance', 'formulariosModulosRespostasService', '$rootScope', 'toaster', 'formulariosModulosService'];

  public documentosNecessariosList: string[];
  public timeout;
  public formEntity: IFormEntity;
  public alterouFormulario: boolean = false;
  public formularioInvalido: boolean = false;
  public busySubmit: boolean = false;

  constructor(public formularioModulo: IFormularioModulo, public respostas: IFormularioRespostas, public $scope: ng.IRootScopeService, public $uibModalInstance: angular.ui.bootstrap.IModalInstanceService, public formulariosModulosRespostasService: FormulariosModulosRespostasService, public $rootScope: ng.IRootScopeService, public toaster: any, public formulariosModulosService: FormulariosModulosService) {
    $scope.$watchGroup(['$ctrl.formulario', '$ctrl.respostas'], () => {
      if (this.formularioModulo && this.respostas) {
        this.montaFormulario();
      }
    });

    this.handleBackdropClick();
  }

  montaFormulario() {
    let secoes = [];

    this.formularioModulo.formulario.formularioitem.forEach((item) => {
      if (item.tipo === ETipoItemFormulario.SECAO) {
        secoes.push(item);
      }
    });

    secoes.forEach((secao) => {
      let childItens = this.formularioModulo.formulario.formularioitem.filter((item) => {
        item.nome = item.formularioitem;
        return item.formularioitempai === secao.formularioitem
      });
      secao.arrItens = childItens;
    });

    this.formEntity = {
      arrSecoes: secoes,
      visualizacao: this.formularioModulo.finalizado_at ? true : false,
    }

    this.verificaValidadeFormulario();
  }

  verificaValidadeFormulario() {
    this.formularioInvalido = this.formularioModulo.formulario.formularioitem.some((item) => item.obrigatorio === 'true' && !item.formularioresposta.resposta)
  }
  
  atualizaFormularioModulo(formularioItem: IFormularioItem) {
    this.formularioModulo.formulario.formularioitem.forEach((item) => {
      if (item.formularioitem === formularioItem.formularioitem) {
        item.formularioresposta.resposta = formularioItem.formularioresposta.resposta;
      }
    });
  }

  entityChange($event) {
    clearTimeout(this.timeout);

    this.timeout = setTimeout(() => {
      this.alterouFormulario = true;
      this.atualizaFormularioModulo($event.detail);
      this.verificaValidadeFormulario();
      this.formulariosModulosRespostasService.constructors = { formulariomodulo: this.formularioModulo.formulariomodulo };
      this.formulariosModulosRespostasService.save($event.detail.formularioresposta);
    }, 600);
  }

  submit() {
    if (this.formularioInvalido) {
      this.toaster.pop({
        type: 'error',
        title: 'É necessário preencher todos os campos obrigatórios para enviar o formulário.'
      });
    } else {
      this.busySubmit = true;
      this.formulariosModulosService.finalizar(this.formularioModulo.formulariomodulo).then(() => {
        this.busySubmit = false;
        this.alterouFormulario = true;
        this.toaster.pop({
          type: 'success',
          title: 'Formulário enviado com sucesso!'
        });
        this.$uibModalInstance.close(this.alterouFormulario);
      });
    }
  }
  
  close(): void {
		this.$uibModalInstance.close(this.alterouFormulario);
	}

  handleBackdropClick() {
    this.$scope.$on('modal.closing', () => {
      this.$rootScope.$broadcast('formularios_modal_closed', this.alterouFormulario);
    });
  }
}