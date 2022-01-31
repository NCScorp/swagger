import angular = require('angular');

import { NsDocumentosFopTemplatesService } from 'assets/ts/modulos/Ns/Documentos-fop-templates/documentos-fop-templates.service';

import { OperacoesDocumentosTemplatesService } from '../../Operacoes-documentos-templates/operacoes-documentos-templates.service';
import {OperacoesOrdensServicosService} from './../operacoes-ordens-servicos.service';

export class OperacoesOrdensServicosEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'operacoesOrdensServicosService',
        'toaster',
        'entity',
        '$http',
        'nsjRouting',
        'nsDocumentosFopTemplatesService',
        'operacoesDocumentosTemplatesService'
    ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    public templates: [];
    public operacoesTemplates: [];

    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public operacoesOrdensServicosService: OperacoesOrdensServicosService,
        public toaster: any,
        public entity: any,
        public $http: any,
        public nsjRouting: any,
        public nsDocumentosFopTemplatesService: NsDocumentosFopTemplatesService,
        public operacoesDocumentosTemplatesService: OperacoesDocumentosTemplatesService
    ) {
        // Artifício para sempre exibir o group dos layouts a serem vinculados.
        this.entity.documentosfoptemplate = [];
        this.buscarTemplates();

        this.action = entity.operacaoordemservico ? 'update' : 'insert';

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

        $scope.$watch('prcsrdnssrvcs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = operacoesOrdensServicosService.constructors;
        $scope.$on('operacoesordensservicos_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir operação de ordem de serviço!';
            let updateSuccess = 'Sucesso ao atualizar operação de ordem de serviço!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('operacoesordensservicos_show', angular.extend({}, this.operacoesOrdensServicosService.constructors, {'operacaoordemservico': args.entity.operacaoordemservico}));
        });
        $scope.$on('operacoesordensservicos_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.operacoesOrdensServicosService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Operacoesordensservicos!' : 'Ocorreu um erro ao inserir Operacoesordensservicos!'
                        });
                }
            }

        });
    }

    submit() {
        this.entity.operacoesdocumentostemplates = this.entity.listaTemplates.filter((t) => {
            return t.selecionado;
        });
        this.entity.documentosfoptemplate = true;
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.operacoesOrdensServicosService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    delete(force: boolean) {

        this.operacoesOrdensServicosService.delete(this.$stateParams.operacaoordemservico, force);
    }

    buscarTemplates() {
        this.entity.loadingTemplates = true;

        this.entity.validarTemplatePadrao = function (template) {
            if (template.padrao) {
                template.selecionado = true;
            }
        }

        this.nsDocumentosFopTemplatesService.filters = {entidade: "servicos.ordensservicos"};
        this.nsDocumentosFopTemplatesService.reload();

        this.$scope.$on('ns_documentosfoptemplates_list_finished', (param) => {
            this.entity.listaTemplates = this.nsDocumentosFopTemplatesService.entities.filter((t) => {
                return t.tenant != 0;
            });

            this.entity.listaTemplates.forEach((template) => {
                if (this.entity.operacoesdocumentostemplates) {
                    let operacaoTemplate = this.entity.operacoesdocumentostemplates.find((o: any) => {
                        return o.documentofoptemplate.documentotemplate == template.documentotemplate;
                    });

                    if (operacaoTemplate) {
                        template.selecionado = true;
                    }
                }
            });

            this.entity.loadingTemplates = false;
        });
    }
}