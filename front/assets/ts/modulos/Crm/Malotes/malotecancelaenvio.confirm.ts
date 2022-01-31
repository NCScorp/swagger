import angular = require('angular');

export class CrmMalotesConfirmMalotecancelaenvioController {
    static $inject = ['entity', '$uibModalInstance'];

    constructor(public entity: any, public $uibModalInstance: any) {
    }

    confirmar() {
        this.$uibModalInstance.close(this.entity);
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

}

export class CrmMalotesConfirmMalotecancelaenvioService {
    static $inject = ['$uibModal'];

    constructor(public $uibModal: any) {
    }

    open(cnfEntity: object) {
        return this.$uibModal.open({
            template: require('./malotecancelaenvio.confirm.html'),
            controller: 'CrmMalotesConfirmMalotecancelaenvioController',
            controllerAs: 'confirm',
            windowClass: '',
            resolve: {
                entity: function () {
                    return cnfEntity;
                }
            }
        });
    }

}
