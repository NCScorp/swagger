import angular = require('angular');
import { EtapasService } from '../etapas.service';
import { IEtapas } from '../models/etapas.model';

export class EtapasEditController implements angular.IController {
  static $inject = [
    'utilService',
    '$scope',
    '$stateParams',
    '$state',
    'etapasService',
    'toaster',
    'entity'
  ];

  public action: string = 'update';
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
    public entity: IEtapas
  ) {
    this.action = entity.projetoetapa ? 'update' : 'insert';

    this.$scope.$on('etapas_deleted', () => {
      this.toaster.pop({
        type: 'success',
        title: 'Sucesso ao excluir etapa!'
      });
      this.$state.go('etapas', angular.extend(this.etapasService.constructors));
    });

    this.$scope.$on('etapas_delete_error', (event: any, args: any) => {
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

    $scope.$watch('qps_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
      if (newValue !== oldValue) {
        this.form.$setDirty();
      }
    }, true);

    this.constructors = etapasService.constructors;
    $scope.$on('etapas_submitted', (event: any, args: any) => {
      let insertSuccess = 'Sucesso ao inserir etapa!';
      let updateSuccess = 'Sucesso ao atualizar etapa!';

      this.toaster.pop({
        type: 'success',
        title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
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
              title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar etapas!' : 'Ocorreu um erro ao inserir etapas!'
            });
        }
      }

    });
  }

  submit() {
    this.form.$submitted = true;
    this.etapasService.save(this.entity);
  }

  delete(force: boolean) {
    this.etapasService.delete(this.$stateParams.projetoetapa, force);
  }
}

