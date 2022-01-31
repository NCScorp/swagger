import angular = require('angular');
export class CrmResponsabilidadesfinanceirasDefaultController {

    static $inject = [                         'CrmResponsabilidadesfinanceirasvaloresFormService',
                                                'CrmResponsabilidadesfinanceirasvaloresFormShowService',
                         '$scope', 'toaster', 'CrmResponsabilidadesfinanceiras', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(                         public CrmResponsabilidadesfinanceirasvaloresFormService: any,
                                                public CrmResponsabilidadesfinanceirasvaloresFormShowService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
                 this.$scope.$on('crm_responsabilidadesfinanceirasvalores_loaded', () => {
            this.busy = false;
        });
            }
       crmResponsabilidadesfinanceirasvaloresForm() {
        let modal = this.CrmResponsabilidadesfinanceirasvaloresFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.responsabilidadesfinanceirasvalores === undefined ) {
                this.entity.responsabilidadesfinanceirasvalores = [subentity];
            } else {
                this.entity.responsabilidadesfinanceirasvalores.push(subentity);
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
    }         crmResponsabilidadesfinanceirasvaloresFormEdit(subentity: any) {
        let parameter = { 'responsabilidadefinanceira': subentity.responsabilidadefinanceira, 'identifier': subentity.responsabilidadefinanceiravalor };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmResponsabilidadesfinanceirasvaloresFormService.open(parameter, subentity);
        modal.result.then(
        (subentity: any) => {
            let key;
            for ( key in this.entity.responsabilidadesfinanceirasvalores ) {
                if ( (this.entity.responsabilidadesfinanceirasvalores[key].responsabilidadefinanceiravalor !== undefined && this.entity.responsabilidadesfinanceirasvalores[key].responsabilidadefinanceiravalor === subentity.responsabilidadefinanceiravalor)
                    || (this.entity.responsabilidadesfinanceirasvalores[key].$id !== undefined && this.entity.responsabilidadesfinanceirasvalores[key].$id === subentity.$id)) {
                    this.entity.responsabilidadesfinanceirasvalores[key] = subentity;
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
    }         crmResponsabilidadesfinanceirasvaloresFormShow(subentity: any) {
        let parameter = { 'responsabilidadefinanceira': subentity.responsabilidadefinanceira, 'identifier': subentity.responsabilidadefinanceiravalor };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmResponsabilidadesfinanceirasvaloresFormShowService.open(parameter, subentity);
    }        submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
        return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.responsabilidadefinanceira = response.data.responsabilidadefinanceira;
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