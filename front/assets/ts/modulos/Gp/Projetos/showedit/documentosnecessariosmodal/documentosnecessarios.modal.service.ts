export class ProjetosDocumentosNecessariosModalService {
	static $inject = ['$uibModal'];

	constructor(public $uibModal: any) { }

	open(documentosNecessarios): void {
		return this.$uibModal.open({
			template: require('./documentosnecessarios.modal.html'),
			controller: 'projetosDocumentosNecessariosModalController',
      controllerAs: '$ctrl',
			resolve: {
				documentosNecessarios: () => {
					return documentosNecessarios;
				},
			}
		});
	}
}
