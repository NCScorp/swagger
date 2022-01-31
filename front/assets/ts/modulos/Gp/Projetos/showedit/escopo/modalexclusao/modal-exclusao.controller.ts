import angular = require('angular');
import { IEscopoNode } from '../../../interfaces/escoponode.interface';

export class ModalExclusaoItemEscopoController {
    static $inject = ['$uibModalInstance', 'entity', '$scope'];

    public action: string;
    public form: any;
    
    constructor(
        public $uibModalInstance: any,
        public entity: IEscopoNode,
        public $scope
    ) {
    }

    delete(confirmDelete: boolean) {
      this.$uibModalInstance.close(confirmDelete);
    }
}
