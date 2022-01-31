import angular = require('angular');
import {OperacoesOrdensServicosService} from '../operacoes-ordens-servicos.service';

export class OperacoesOrdensServicosShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'operacoesOrdensServicosService',
        'toaster',
        'entity',
    ];

    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public operacoesOrdensServicosService: OperacoesOrdensServicosService,
        public toaster: any,
        public entity: any,
    ) {


        this.$scope.$on('operacoesordensservicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir operação de ordem de serviço!'
            });
            this.$state.go('operacoesordensservicos', angular.extend(this.operacoesOrdensServicosService.constructors));
        });

        this.$scope.$on('operacoesordensservicos_delete_error', (event: any, args: any) => {
            if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: args.response.data.message
                    });
            } else {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: 'Ocorreu um erro ao tentar excluir.'
                    });
            }
        });

        this.constructors = operacoesOrdensServicosService.constructors;

        // Artifício para exibir o group com os Layouts selecionados.
        // Dessa forma, só exibe o group quando houver algum template vinculado.
        // Quando houver apenas o template default, não exibe o group.
        this.entity.documentosfoptemplate = (this.entity?.operacoesdocumentostemplates?.length > 1) ? [] : null;
    }

    delete(force: boolean) {

        this.operacoesOrdensServicosService.delete(this.$stateParams.operacaoordemservico, force);
    }


}
