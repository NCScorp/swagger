import { ui } from 'angular';

export class DesfazerContaPagarModalController {
  static $inject = [
    '$uibModalInstance',
    'entity',
    'constructors',
    'nsjRouting',
    '$scope',
    'toaster',
    '$http',
  ];

  /**
   * Define se a tela está em processamento
   */
  private busy: boolean = false;

  constructor(
    public $uibModalInstance: ui.bootstrap.IModalInstanceService,
    public entity: any,
    public constructors: any,
    public nsjRouting: any,
    public $scope: any,
    public toaster: any,
    public $http: any
  ) {}

  $onInit() {
    // Apresento loading de carregamento
    this.busy = false;
  }

  isBusy(): boolean {
    return this.busy;
  }

  defazerContaPagar(){
    this.$uibModalInstance.close(this.entity.linha[0].contapagar)
  }

  close(){
      this.$uibModalInstance.close(null)
  }
}
