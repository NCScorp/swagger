import angular = require('angular');
import {OperacoesDocumentosTemplatesService} from "../operacoes-documentos-templates.service";

export class OperacoesDocumentosTemplatesEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'operacoesDocumentosTemplatesService',
        'toaster',
        'entity'
    ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public operacoesDocumentosTemplatesService: OperacoesDocumentosTemplatesService,
        public toaster: any,
        public entity: any)
    {
        this.action = entity.operacaodocumentotemplate ? 'update' : 'insert';

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

        $scope.$watch('prcsdcmntstmplts_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = operacoesDocumentosTemplatesService.constructors;
        $scope.$on('operacoesdocumentostemplates_submitted', (event: any, args: any) => {
            let insertSuccess = 'O relacionamento entre a Operação e o Template foi realizado com sucesso!';
            let updateSuccess = 'Sucesso ao atualizar Operacoesdocumentostemplates!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('operacoesdocumentostemplates_show', angular.extend({}, this.operacoesDocumentosTemplatesService.constructors, {'operacaodocumentotemplate': args.entity.operacaodocumentotemplate}));
        });
        $scope.$on('operacoesdocumentostemplates_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.operacoesDocumentosTemplatesService.save(this.entity);
                }
            } else {
                if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    if (args.response.data.message === 'Validation Failed') {
                        let distinct = (value: any, index: any, self: any) => {
                            return self.indexOf(value) === index;
                        };
                        args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                        let message = this.utilService.parseValidationMessage(args.response.data);
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: 'Erro de Validação',
                                body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                bodyOutputType: 'trustedHtml'
                            });
                    } else {
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: args.response.data.message
                            });
                    }
                } else {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Operacoesdocumentostemplates!' : 'Ocorreu um erro ao inserir Operacoesdocumentostemplates!'
                        });
                }
            }

        });
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.operacoesDocumentosTemplatesService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    delete(force: boolean) {

        this.operacoesDocumentosTemplatesService.delete(this.$stateParams.operacaodocumentotemplate, force);
    }


}
