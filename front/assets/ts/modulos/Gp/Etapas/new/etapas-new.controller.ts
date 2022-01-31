import angular = require('angular');
import { EtapasService } from '../etapas.service';
export class EtapasNewController implements angular.IController {
  static $inject = [
    'utilService',
    '$scope',
    '$stateParams',
    '$state',
    'etapasService',
    'toaster',
    'entity'
  ];

  public action: string = 'insert';
  public busy: boolean = false;
  public form: angular.IFormController;
  public constructors: any;

  constructor(
    public utilService: any,
    public $scope: angular.IScope,
    public $stateParams: angular.ui.IStateParamsService,
    public $state: angular.ui.IStateService,
    public etapasService: EtapasService,
    public toaster: any,
    public entity: any
  ) {

    this.constructors = etapasService.constructors;
    $scope.$on('etapas_submitted', (event: any, args: any) => {
      this.toaster.pop({
        type: 'success',
        title: 'Sucesso ao inserir etapa!'
      });
      this.$state.go('etapas_show', angular.extend({}, this.etapasService.constructors, { 'projetoetapa': args.entity.projetoetapa }));
    });
    $scope.$on('etapas_submit_error', (event: any, args: any) => {
      if (args.response.status === 409) {
        if (confirm(args.response.data.message)) {
          this.entity[''] = args.response.data.entity[''];
          this.etapasService.save(this.entity);
        }
      } else {
        if (typeof (args.response.data) !== 'undefined' && args.response.data.message) {
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
              title: 'Ocorreu um erro ao inserir etapas!'
            });
        }
      }

    });
  }

  submit() {
    this.form.$submitted = true;

    this.etapasService.save(this.entity);
  }
}
