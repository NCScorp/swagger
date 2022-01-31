import { IEscopoNode } from "../../../interfaces/escoponode.interface";

export class ModalExclusaoItemEscopoService {
  static $inject = ['$uibModal', 'nsjRouting', '$http'];

  constructor(public $uibModal: any, public nsjRouting: any, public $http: any) {
  }

  open(entity: IEscopoNode) {
    return this.$uibModal.open({
      template: require('./modal-exclusao.html'),
      controller: 'modalExclusaoItemEscopoController',
      controllerAs: '$ctrl',
      resolve: {
        entity: () => {
          return entity;
        }
      }
    });
  }
}
