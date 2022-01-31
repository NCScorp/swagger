import angular = require('angular');
import { CrmTemplatespropostas } from './factory';
export class CrmTemplatespropostasDefaultController {

    static $inject = [
        'CrmTemplatespropostasdocumentosFormService',
        'CrmTemplatespropostasdocumentosFormShowService',
        '$scope',
        'toaster',
        'CrmTemplatespropostas',
        'utilService'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public entityDocumento: any;

    public listaDocumentosConfig = {
        nome: 'Nome',
        tipocopiadocumento: 'Tipo de cópia',
        enviarporemail: 'Enviar por e-mail',
        pedirinformacoesadicionais: 'Pedir informações adicionais',
        naoexibiremrelatorios: 'Não exibir nos relatórios de documentos automáticos',
        actions: null
    }


    constructor(
        public CrmTemplatespropostasdocumentosFormService: any,
        public CrmTemplatespropostasdocumentosFormShowService: any,
        public $scope: angular.IScope,
        public toaster: any,
        public entityService: CrmTemplatespropostas,
        public utilService: any
    ) {
        this.montaListaDocumentos = this.montaListaDocumentos.bind(this);
    }


    montaListaDocumentos(documento) {
        const config = {
            entity: {
                nome: documento.tipodocumento.nome,
                tipocopiadocumento: this.getDocumentos(documento),
                enviarporemail: documento.permiteenvioemail ? 'Sim' : 'Não',
                pedirinformacoesadicionais: documento.pedirinformacoesadicionais ? 'Sim' : 'Não',
                naoexibiremrelatorios: documento.naoexibiremrelatorios ? 'Sim' : 'Não',
            },
            actions:
                [
                    {
                        label: 'Editar',
                        icon: 'fas fa-edit',
                        method: (subentity: any, index: any) => {
                            this.editarDocumentoDaLista(subentity, index)
                        }
                    },
                    {
                        label: 'Excluir',
                        icon: 'fas fa-trash-alt',
                        method: (subentity: any, index: any) => {
                            this.removerDocumentoDaLista(subentity, index)
                        }
                    }
                ]
        }

        //Se não for a edição de documentos, não é exibida nenhuma ação sobre os documentos
        if (!this.constructors.editarDocumentos) {
            delete config.actions;
        }

        return config;
    }

    getDocumentos(documento) {
        let textoArr = [];

        if (documento.copiasimples) {
            textoArr.push('Simples');
        }
        if (documento.copiaautenticada) {
            textoArr.push('Autenticada');
        }
        if (documento.original) {
            textoArr.push('Original');
        }

        const textoFinal = textoArr.join(', ').replace(/, ([^,]*)$/, ' e $1');

        return textoFinal;
    }

    atualizarListaDocumentos(listaDocumentos) {
        this.entity.templatespropostasdocumentos = [...listaDocumentos];
    }

    editarDocumentoDaLista(subentity, index) {
        this.crmTemplatespropostasdocumentosFormEdit(subentity);
    }

    removerDocumentoDaLista(subentity, index) {
        this.entity.templatespropostasdocumentos = this.entity.templatespropostasdocumentos.filter((item, i) => i !== index);
        this.$scope.$applyAsync();

    }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
        this.$scope.$on('crm_templatespropostasdocumentos_loaded', () => {
            this.busy = false;
        });
    }
    crmTemplatespropostasdocumentosForm() {
        let modal = this.CrmTemplatespropostasdocumentosFormService.open({}, {}, angular.copy(this.entity.templatespropostasdocumentos));
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.templatespropostasdocumentos === undefined) {
                this.entity.templatespropostasdocumentos = [subentity];
            } else {
                this.entity.templatespropostasdocumentos = [...this.entity.templatespropostasdocumentos, subentity];
            }
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

    crmTemplatespropostasdocumentosFormEdit(subentity: any) {

        var modal = this.CrmTemplatespropostasdocumentosFormService.open({}, subentity, angular.copy(this.entity.templatespropostasdocumentos));
        modal.result.then(
            (subentity: any) => {

                //Se a subentity tiver a propriedade, uso ela para buscar o elemento que foi editado
                if (subentity.hasOwnProperty('templatepropostadocumento')) {

                    for (let i = 0; i < this.entity.templatespropostasdocumentos.length; i++) {
                        //Documento que será atualizado
                        if (this.entity.templatespropostasdocumentos[i].templatepropostadocumento == subentity.templatepropostadocumento) {
                            this.entity.templatespropostasdocumentos[i] = { ...subentity };
                            break;
                        }
                    }
                }
                //Se não tiver a propriedade, uso a propriedade $$id
                else {

                    for (let i = 0; i < this.entity.templatespropostasdocumentos.length; i++) {
                        //Documento que será atualizado
                        if (this.entity.templatespropostasdocumentos[i].$$id == subentity.$$id) {
                            this.entity.templatespropostasdocumentos[i] = { ...subentity };
                            break;
                        }
                    }
                }

                this.atualizarListaDocumentos(this.entity.templatespropostasdocumentos);

            })
            .catch((error: any) => {
                this.busy = false;
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    crmTemplatespropostasdocumentosFormShow(subentity: any) {
        let parameter = { 'templateproposta': subentity.templateproposta, 'identifier': subentity.templatepropostadocumento };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.CrmTemplatespropostasdocumentosFormShowService.open(parameter, subentity);
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.templateproposta = response.data.templateproposta;
                    resolve(this.entity);
                })
                    .catch((response: any) => {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let message = this.utilService.parseValidationMessage(response.data.errors.children);
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
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop(
                                {
                                    type: 'error',
                                    title: 'Erro ao adicionar.'
                                });
                        }
                    });
            });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    uniqueValidation(params: any): any {
        if (this.collection) {
            let validation = { 'unique': true };
            for (var item in this.collection) {
                if (this.collection[item][params.field] === params.value) {
                    validation.unique = false;
                    break;
                }
            }
            return validation;
        } else {
            return null;
        }
    }
}
