import angular = require('angular');
import { TemplateOrdemServicoFormView } from '../interfaces/template-os.form';
import { ITemplateOrdemServico } from '../interfaces/templates-os.interface';

import { TemplatesOrdemServicoService } from '../templates-os.service';

export class TemplatesOrdemServicoNewController {
  static $inject = [
    'utilService',
    '$scope',
    '$stateParams',
    '$state',
    'templatesOrdemServicoService',
    'toaster',
  ];

  public entity: TemplateOrdemServicoFormView = null;
  public action: string = 'insert';
  public busy: boolean = false;
  public form: any;
  public constructors: any;

  constructor(
    public utilService: any,
    public $scope: angular.IScope,
    public $stateParams: angular.ui.IStateParamsService,
    public $state: angular.ui.IStateService,
    public templatesOrdemServicoService: TemplatesOrdemServicoService,
    public toaster: any
  ) {
    this.constructors = templatesOrdemServicoService.constructors;
    this.entity = new TemplateOrdemServicoFormView(<ITemplateOrdemServico>{});
  }

  $onInit() {
    this.onSubmitSuccess();
    this.onSubmitError();
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
        title: 'Sucesso ao template de ordem de serviço!'
      });
      this.$state.go('ordensservicos_templates_show', angular.extend({}, this.templatesOrdemServicoService.constructors, { 'ordemservicotemplate': args.entity.ordemservicotemplate }));
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
            this.toaster.pop(
              {
                type: 'error',
                title: 'Erro de Validação',
                body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                bodyOutputType: 'trustedHtml'
              });
          } else {
            this.toaster.pop(
              {
                type: 'error',
                title: args.response.data.message
              });
          }
        } else {
          this.toaster.pop(
            {
              type: 'error',
              title: 'Ocorreu um erro ao inserir template de ordem de serviço!'
            });
        }
      }

    });
  }
}
