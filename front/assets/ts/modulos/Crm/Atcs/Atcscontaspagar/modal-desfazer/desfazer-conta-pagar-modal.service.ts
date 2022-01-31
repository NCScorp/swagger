import { ui } from 'angular';

export class DesfazerContaPagarModalService {
  static $inject = ['$uibModal', '$http', 'nsjRouting', 'toaster'];

  constructor(
    public $uibModal: ui.bootstrap.IModalService,
    public $http: angular.IHttpService,
    public nsjRouting: any,
    public toaster: any
  ) {}

  open(parameters: any = {}, subentity: any) {
    return this.$uibModal.open({
      template: require('./desfazer-conta-pagar-modal.html'),
      controller: 'desfazerContaPagarModalController',
      controllerAs: 'crm_atcs_dsfzr_cnt_pgr_ctrl',
      windowClass: '',
      size: 'lg',
      resolve: {
        entity: subentity,
        constructors: () => {
          return parameters;
        },
      },
    });
  }
}
