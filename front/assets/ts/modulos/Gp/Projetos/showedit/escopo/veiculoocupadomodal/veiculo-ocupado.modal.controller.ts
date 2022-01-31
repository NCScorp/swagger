import angular = require('angular');
import moment from 'moment';

import { veiculoOcupadoType } from './veiculo-ocupado.modal.service';

export class VeiculoOcupadoModalController {

  static $inject = ['$scope', 'entities', '$uibModalInstance'];

	public fieldsConfig = [
		{
			value: 'projeto.nome',
			label: 'Projeto',
			type: 'string',
			style: 'title',
			copy: '',
		},
		{
			value: 'descricao',
			label: 'Descrição',
			type: 'string',
			style: 'title',
		},
    {
			value: 'datahorainicioDisplay',
			label: 'Início',
			type: 'string',
      style: 'title'
    },
    {
			value: 'datahorafimDisplay',
			label: 'Fim',
			type: 'string',
			style: 'title',
		}
	];

  constructor(public $scope: angular.IScope, public entities: veiculoOcupadoType[], public $uibModalInstance) {
    $scope.$watch('$ctrl.entities', () => {
      entities.map((entity) => {
          entity.datahorainicioDisplay = `${moment(entity.datahorainicio).format('DD/MM/YYYY')} às ${moment(entity.datahorainicio).format('HH:mm')}`;
          entity.datahorafimDisplay = `${moment(entity.datahorafim).format('DD/MM/YYYY')} às ${moment(entity.datahorafim).format('HH:mm')}`;
      })
    })
  }

  close(continuarComAtribuicao: boolean) {
    this.$uibModalInstance.close(continuarComAtribuicao);
  }
}
