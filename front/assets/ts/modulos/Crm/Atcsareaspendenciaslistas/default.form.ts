import angular = require('angular');
export class CrmAtcsareaspendenciaslistasDefaultController {

    static $inject = [                         'CrmAtcsareaspendenciasFormService',
                                                'CrmAtcsareaspendenciasFormShowService',
                         '$scope', 'toaster', 'CrmAtcsareaspendenciaslistas', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(                         public CrmAtcsareaspendenciasFormService: any,
                                                public CrmAtcsareaspendenciasFormShowService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
                 this.$scope.$on('crm_atcsareaspendencias_loaded', () => {
            this.busy = false;
        });
            }
       crmAtcsareaspendenciasForm() {
        let modal = this.CrmAtcsareaspendenciasFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.pendencias === undefined ) {
                this.entity.pendencias = [subentity];
            } else {
                this.entity.pendencias.push(subentity);
            }
        })
        .catch( (error: any) => {
            if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                this.toaster.pop({
                        type: 'error',
                        title: error
                    });
            }
        });
    }         crmAtcsareaspendenciasFormEdit(subentity: any) {
        let parameter = { 'negocioareapendencialista': subentity.negocioareapendencialista, 'identifier': subentity.negocioareapendencia };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmAtcsareaspendenciasFormService.open(parameter, subentity);
        modal.result.then(
        (subentity: any) => {
            let key;
            for ( key in this.entity.pendencias ) {
                if ( (this.entity.pendencias[key].negocioareapendencia !== undefined && this.entity.pendencias[key].negocioareapendencia === subentity.negocioareapendencia)
                    || (this.entity.pendencias[key].$id !== undefined && this.entity.pendencias[key].$id === subentity.$id)) {
                    this.entity.pendencias[key] = subentity;
                }
            }
        })
        .catch( (error: any) => {
            this.busy = false;
            if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                this.toaster.pop({
                        type: 'error',
                        title: error
                    });
            }
        });
    }         crmAtcsareaspendenciasFormShow(subentity: any) {
        let parameter = { 'negocioareapendencialista': subentity.negocioareapendencialista, 'identifier': subentity.negocioareapendencia };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmAtcsareaspendenciasFormShowService.open(parameter, subentity);
    }        submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
        return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.negocioareapendencialista = response.data.negocioareapendencialista;
                    resolve(this.entity);
                })
                .catch((response: any) => {
                    if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                        if ( response.data.message === 'Validation Failed' ) {
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
        if ( this.collection ) {
            let validation = { 'unique' : true };
            for ( var item in this.collection ) {
                if ( this.collection[item][params.field] === params.value ) {
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