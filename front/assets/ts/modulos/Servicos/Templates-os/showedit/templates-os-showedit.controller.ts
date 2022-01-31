import angular = require('angular');
import { TemplatesOrdemServicoService } from '../templates-os.service';
import { TemplateOrdemServicoFormView } from '../interfaces/template-os.form';
import { TemplatesOrdemServicoItemService } from '../itens/templates-os-item.service';
import { TemplatesOrdemServicoMaterialService } from '../materiais/templates-os-materiais.service';
import { TemplateOrdemServicoItemFormView } from '../itens/interfaces/template-os-item.form';
import { TemplateOrdemServicoMaterialFormView } from '../materiais/interfaces/template-os-material.form';

export class templatesOrdemServicoShowEditController {
  static $inject = [
    'utilService',
    '$scope',
    '$stateParams',
    '$state',
    'templatesOrdemServicoService',
    'toaster',
    'entity',
    'templatesOrdemServicoItemService',
    'templatesOrdemServicoMaterialService'
  ];

  public action: string = 'retrieve';
  public form;
  public busy: boolean = false;
  public isEditing: boolean = false;
  public constructors: any;

  constructor(
    public utilService: any,
    public $scope: angular.IScope,
    public $stateParams: angular.ui.IStateParamsService,
    public $state: angular.ui.IStateService,
    public templatesOrdemServicoService: TemplatesOrdemServicoService,
    public toaster: any,
    public entity: TemplateOrdemServicoFormView,
    public templatesOrdemServicoItemService: TemplatesOrdemServicoItemService,
    public templatesOrdemServicoMaterialService: TemplatesOrdemServicoMaterialService
  ) {
    this.constructors = templatesOrdemServicoService.constructors;
  }

  async $onInit() {
    this.onSubmitSuccess();
    this.onSubmitError();
    this.onDeleteSuccess();
    this.onDeleteError();

    this.busy = true;
    this.reloadScope();

    // Busco itens e materiais do template da Api
    const [arrItensApi, arrMateriaisApi] = await Promise.all([
      this.templatesOrdemServicoItemService.getAll({}, { ordemservicotemplate: this.entity.ordemservicotemplate }),
      this.templatesOrdemServicoMaterialService.getAll({}, { ordemservicotemplate: this.entity.ordemservicotemplate })
    ]);

    // Realizo conversão dos dados
    this.entity.arrItens = arrItensApi.map((item) => {
      return new TemplateOrdemServicoItemFormView(item);
    });
    this.entity.arrMateriais = arrMateriaisApi.map((material) => {
      return new TemplateOrdemServicoMaterialFormView(material);
    })

    // Desativo loading
    this.busy = false;
    this.reloadScope();
  }

  submit() {
    this.form.$submitted = true;
    if (this.form.$valid && !(<any>this.entity).$$__submitting) {
      this.templatesOrdemServicoService.save(this.entity);
    } else {
      this.toaster.pop({
        type: 'error',
        title: 'Alguns campos do formulário apresentam erros.'
      });
    }
  }

  onSubmitSuccess() {
    this.$scope.$on('ordensservicos_templates_submitted', (event: any, args: any) => {
      this.toaster.pop({
        type: 'success',
        title: 'Sucesso ao editar o template de ordem de serviço!'
      });
      this.setEditing(false);
    });
  }

  onSubmitError() {
    this.$scope.$on('ordensservicos_templates_submit_error', (event: any, args: any) => {
      if (args.response.status === 409) {
        if (confirm(args.response.data.message)) {
          this.entity[''] = args.response.data.entity[''];
          this.templatesOrdemServicoService.save(this.entity);
        }
      } else {
        if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
          if (args.response.data.message === 'Validation Failed') {
            let distinct = (value: any, index: any, self: any) => {
              return self.indexOf(value) === index;
            };
            args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
            let message = this.utilService.parseValidationMessage(args.response.data);
            this.toaster.pop({
              type: 'error',
              title: 'Erro de Validação',
              body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
              bodyOutputType: 'trustedHtml'
            });
          } else {
            this.toaster.pop({
              type: 'error',
              title: args.response.data.message
            });
          }
        } else {
          this.toaster.pop({
            type: 'error',
            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar template de ordem de serviço!' : 'Ocorreu um erro ao inserir template de ordem de serviço!'
          });
        }
      }

    });
  }

  onDeleteSuccess() {
    this.$scope.$on('ordensservicos_templates_deleted', (event: any, args: any) => {
      this.toaster.pop({
        type: 'success',
        title: 'Sucesso ao excluir template de ordem de serviço!'
      });
      this.$state.go('ordensservicos_templates', angular.extend(this.templatesOrdemServicoService.constructors));
    });
  }

  onDeleteError() {
    this.$scope.$on('ordensservicos_templates_delete_error', (event: any, args: any) => {
      if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
        this.toaster.pop(
          {
            type: 'error',
            title: args.response.data.message
          });
      } else {
        this.toaster.pop(
          {
            type: 'error',
            title: 'Ocorreu um erro ao tentar excluir.'
          });
      }
    });
  }

  delete(force: boolean) {
    this.templatesOrdemServicoService.delete(this.entity.ordemservicotemplate, force);
  }

  reloadScope() {
    this.$scope.$applyAsync();
  }

  onSalvarTemplate(template: any): Promise<any> {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        template['ordemservicotemplate'] = 'xpto';
        
        resolve(template);
      }, 3000);
    });
  }

  setEditing(isEditing) {
    this.isEditing = isEditing;
  }
}
