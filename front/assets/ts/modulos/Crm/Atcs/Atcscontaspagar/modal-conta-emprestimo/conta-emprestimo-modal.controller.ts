import { IHttpService, ui } from 'angular';
import { IConta } from 'assets/ts/modulos/Financas/Contas/classes/contas.interfaces';

export class ContaEmprestimoModalController {
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
  private contaEmprestimo: IConta;

  constructor(
    public $uibModalInstance: ui.bootstrap.IModalInstanceService,
    public entity: any,
    public constructors: any,
    public nsjRouting: any,
    public $scope: any,
    public toaster: any,
    public $http: IHttpService
  ) {}

  $onInit() {
    // Apresento loading de carregamento
    this.busy = false;
  }

  isBusy(): boolean {
    return this.busy;
  }

  salvarContaEmprestimo() {
    this.$uibModalInstance.close(this.contaEmprestimo);
  }

  close() {
    this.$uibModalInstance.close(null);
  }
}
