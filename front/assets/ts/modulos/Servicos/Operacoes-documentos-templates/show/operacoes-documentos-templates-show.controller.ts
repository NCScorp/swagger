import angular = require('angular');
import {OperacoesDocumentosTemplatesService} from "../operacoes-documentos-templates.service";

export class OperacoesDocumentosTemplatesShowController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'operacoesDocumentosTemplatesService',
        'toaster',
        'entity'
    ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public operacoesDocumentosTemplatesService: OperacoesDocumentosTemplatesService,
        public toaster: any,
        public entity: any
    ) {


        this.$scope.$on('operacoesdocumentostemplates_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O relacionamento entre a Operação e o Template foi excluído com sucesso!'
            });
            this.$state.go('operacoesdocumentostemplates', angular.extend(this.operacoesDocumentosTemplatesService.constructors));
        });

        this.$scope.$on('operacoesdocumentostemplates_delete_error', (event: any, args: any) => {
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

        this.constructors = operacoesDocumentosTemplatesService.constructors;
    }

    delete(force: boolean) {

        this.operacoesDocumentosTemplatesService.delete(this.$stateParams.operacaodocumentotemplate, force);
    }


}
