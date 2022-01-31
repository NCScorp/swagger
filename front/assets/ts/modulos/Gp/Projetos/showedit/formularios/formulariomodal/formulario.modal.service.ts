import { IFormularioModulo, IFormularioRespostas } from "../formularios.interface";

export class FormularioModalService {
	static $inject = ['$uibModal'];

	constructor(public $uibModal: any) { }

	open(formularioModulo: IFormularioModulo, respostas: IFormularioRespostas[]) {
		return this.$uibModal.open({
			template: require('./formulario.modal.html'),
			controller: 'formularioModalController',
      controllerAs: '$ctrl',
			resolve: {
				formularioModulo: () => {
					return formularioModulo;
				},
        respostas: () => {
          return respostas;
        }
			}
		});
	}
}
