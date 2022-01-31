import angular = require('angular');
export class CrmListadavezvendedoresShowShowController {

    static $inject = [                         'CrmListadavezvendedoresitensFormService',
                                                'CrmListadavezvendedoresitensFormShowService',
                         '$scope', 'toaster', 'CrmListadavezvendedores', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(                         public CrmListadavezvendedoresitensFormService: any,
                                                public CrmListadavezvendedoresitensFormShowService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
                 this.$scope.$on('crm_listadavezvendedoresitens_loaded', () => {
            this.busy = false;
        });
            }
       crmListadavezvendedoresitensForm() {
        let modal = this.CrmListadavezvendedoresitensFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.itens === undefined ) {
                this.entity.itens = [subentity];
            } else {
                this.entity.itens.push(subentity);
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
    }         crmListadavezvendedoresitensFormEdit(subentity: any) {
        let parameter = { 'listadavezvendedor': subentity.listadavezvendedor.listadavezvendedor, 'identifier': subentity.listadavezvendedoritem };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmListadavezvendedoresitensFormService.open(parameter, subentity);
        modal.result.then(
        (subentity: any) => {
            let key;
            for ( key in this.entity.itens ) {
                if ( (this.entity.itens[key].listadavezvendedoritem !== undefined && this.entity.itens[key].listadavezvendedoritem === subentity.listadavezvendedoritem)
                    || (this.entity.itens[key].$id !== undefined && this.entity.itens[key].$id === subentity.$id)) {
                    this.entity.itens[key] = subentity;
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
    }         crmListadavezvendedoresitensFormShow(subentity: any) {
        let parameter = { 'listadavezvendedor': subentity.listadavezvendedor.listadavezvendedor, 'identifier': subentity.listadavezvendedoritem };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmListadavezvendedoresitensFormShowService.open(parameter, subentity);
    }        uniqueValidation(params: any): any {
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