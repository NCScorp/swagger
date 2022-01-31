import { IProjeto } from "../../../interfaces/projetos.interface";

export type veiculoOcupadoType = {
    descricao: string;
    datahorainicioDisplay?: string;
    datahorafimDisplay?: string;
    datahorainicio: string;
    datahorafim: string;
    projeto: IProjeto
}

export class VeiculoOcupadoModalService {
  static $inject = ['$uibModal', 'nsjRouting'];

  constructor(public $uibModal: any, public nsjRouting: any) {
  }

  open(entities: veiculoOcupadoType[]) {
    return this.$uibModal.open({
      template: require('./veiculo-ocupado.modal.html'),
      controller: 'veiculoOcupadoModalController',
      controllerAs: '$ctrl',
      resolve: {
        entities: () => {
          return entities;
        }
      }
    });
  }
}
