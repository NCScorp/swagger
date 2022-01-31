import angular = require('angular');
import { CrmAtcsareas } from '././../../Crm/Atcsareas/factory';

export class CrmAtcsareasDefaultController {
    static $inject = [
        '$scope',
        'toaster',
        'CrmAtcsareas',
        'utilService',
        'CrmAtcsareaspendenciaslistasFormService',
        'CrmAtcsareaspendenciaslistasFormShowService',
        'CrmAtcsareas',
        'CrmAtcsareaspendenciaslistas',
        'CrmAtcsareaspendencias'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public newEntity: any;
    public tooltipPermiteorcamentozerado: string = 'Permitir valores zerados ao preencher o orçamento no atendimento comercial, desde que a prestadora pertença ao estabelecimento.';

    constructor(public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any, public CrmAtcsareaspendenciaslistasFormService: any, public CrmAtcsareaspendenciaslistasFormShowService: any, public CrmAtcsareas: any, public CrmAtcsareaspendenciaslistas: any, public CrmAtcsareaspendencias: any) {
        this.montaListaPendenciaslistas = this.montaListaPendenciaslistas.bind(this);
    }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        if (this.entity.negocioarea != null) {
            this.CrmAtcsareaspendenciaslistas.constructors = { 'negocioarea': this.entity.negocioarea };
            this.CrmAtcsareaspendenciaslistas.reload();
        }

        this.$scope.$on('crm_atcsareaspendenciaslistas_list_finished', (event: any, args: any) => {
            this.entity.negociosareaspendenciaslistas = args;
            this.entity.negociosareaspendenciaslistasoriginal = args;

        });

    }

    public pendenciaslistasConfig = {
        nome: 'Listas de pendências',
        acoes: null
    }

    crmPendenciaslistasForm() {

        let modal = this.CrmAtcsareaspendenciaslistasFormService.open({}, {});
        modal.result.then((subentity: any) => {

            if (this.entity.negociosareaspendenciaslistas === undefined) {

                this.entity.negociosareaspendenciaslistas = [subentity];

            } else {

                this.newEntity = subentity;

            }
        }).catch((error: any) => {
            this.busy = false;
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }

    montaListaPendenciaslistas(subentity: any) {

        const config = {

            acoes: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.crmPendenciaslistasFormShow(subentity)
                    }
                },
                {
                    label: 'Editar',
                    icon: 'fas fa-edit',
                    method: (subentity: any, index: any) => {
                        this.crmPendenciaslistasFormEdit(subentity)
                    }
                },
                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (subentity: any, index: any) => {
                        this.crmremovePendenciaslista(subentity, index)
                    }
                }
            ]
        }

        return config;
    }

    atualizaPendenciaslistas(pendenciaslistas) {
        this.entity.negociosareaspendenciaslistas = pendenciaslistas;
    }

    crmPendenciaslistasFormEdit(subentity: any) {
        let parameter = { 'atcarea': subentity.negocioarea, 'identifier': subentity.negocioareapendencialista };
        this.busy = true;
        this.CrmAtcsareaspendenciaslistas.get(this.entity.negocioarea, subentity.negocioareapendencialista);
        this.$scope.$on('crm_atcsareaspendenciaslistas_loaded', (event: any, args: any) => {
            let originalEntity = args;
            subentity = angular.merge(subentity, originalEntity);
            this.busy = false;
            var modal = this.CrmAtcsareaspendenciaslistasFormService.open(parameter, subentity);
            modal.result.then(
                (subentity: any) => {
                    subentity.editado = true;
                    this.newEntity = subentity;
                }).catch((error: any) => {
                    this.busy = false;
                    if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                        this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                    }
                });

        });

    }

    crmPendenciaslistasFormShow(subentity: any) {
        let parameter = { 'atcarea': subentity.negocioarea, 'identifier': subentity.negocioareapendencialista };

        if (!subentity) {
            this.busy = true;
        }
        var modal = this.CrmAtcsareaspendenciaslistasFormShowService.open(parameter, subentity);
    }

    crmremovePendenciaslista(entity: any, index: number) {
        this.entity.negociosareaspendenciaslistas.splice(index, 1);
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.negocioarea = response.data.negocioarea;
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