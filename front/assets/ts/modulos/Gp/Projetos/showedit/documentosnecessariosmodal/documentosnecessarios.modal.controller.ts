export class ProjetosDocumentosNecessariosModalController implements ng.IController {
  static $inject = ['documentosNecessarios', '$scope', '$uibModalInstance'];

  public documentosNecessariosList: string[];

  constructor(public documentosNecessarios, public $scope: ng.IRootScopeService, public $uibModalInstance: angular.ui.bootstrap.IModalInstanceService) {
    $scope.$watch('$ctrl.documentosNecessarios', () => {
      this.filtraDocumentosDuplicados();
    });
  }

  /**
   * Filtra lista de documentos para que não existam elementos duplicados
   */
  filtraDocumentosDuplicados(): void {
    this.documentosNecessariosList = this.documentosNecessarios.map((element) => element.tipodocumento.nome).filter((value, index, arr) => {
      return arr.indexOf(value) === index;
    });
  }
  
  close(): void {
		this.$uibModalInstance.dismiss();
	}
}