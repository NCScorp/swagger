import angular = require('angular');
export class NsProspectsDefaultController {

    static $inject = [                         'NsContatosFormService',
                                                'NsContatosFormShowService',
                         '$scope', 'toaster', 'NsProspects', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(                         public NsContatosFormService: any,
                                                public NsContatosFormShowService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
                 this.$scope.$on('ns_contatos_loaded', () => {
            this.busy = false;
        });
            }
       nsContatosForm() {
        let modal = this.NsContatosFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.contatos === undefined ) {
                this.entity.contatos = [subentity];
            } else {
                this.entity.contatos.push(subentity);
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
    }         nsContatosFormEdit(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.NsContatosFormService.open(parameter, subentity);
        modal.result.then(
        (subentity: any) => {
            let key;
            for ( key in this.entity.contatos ) {
                if ( (this.entity.contatos[key].contato !== undefined && this.entity.contatos[key].contato === subentity.contato)
                    || (this.entity.contatos[key].$id !== undefined && this.entity.contatos[key].$id === subentity.$id)) {
                    this.entity.contatos[key] = subentity;
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
    }         nsContatosFormShow(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.NsContatosFormShowService.open(parameter, subentity);
    }        submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
        return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.prospect = response.data.prospect;
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