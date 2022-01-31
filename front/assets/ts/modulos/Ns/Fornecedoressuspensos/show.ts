import angular = require('angular');

export class NsFornecedoressuspensosFormShowController {

    static $inject = [
        'NsFornecedoressuspensosFormFornecedorreativarService',
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'NsFornecedoressuspensos',
        'toaster',
        'entity',
        '$rootScope'];

    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(public NsFornecedoressuspensosFormFornecedorreativarService: any,
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public entityService: any,
        public toaster: any,
        public entity: any,
        public $rootScope: any) {
        this.constructors = entityService.constructors;
    }

    fornecedorreativar(entity: any) {
        let modal = this.NsFornecedoressuspensosFormFornecedorreativarService.open({ 'fornecedorsuspenso': entity.fornecedorsuspenso });
        modal.result.then(() => {
            this.$state.go('ns_fornecedoressuspensos', angular.extend(this.entityService.constructors));
            this.$rootScope.$broadcast('ns_fornecedoressuspensos_deleted');
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    // fornecedorreativar(entity: any) {
    //     let modal = this.NsFornecedoressuspensosFormFornecedorreativarService.open({ 'fornecedorsuspenso': entity.fornecedorsuspenso });
    //     modal.result.then(() => {
    //         this.$state.reload();
    //     })
    //         .catch((error: any) => {
    //             if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
    //                 this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //             }
    //         });
    // }
}
