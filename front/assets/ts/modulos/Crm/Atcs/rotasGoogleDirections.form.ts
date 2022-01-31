import angular = require('angular');
export class CrmAtcsRotasgoogledirectionsController {

    static $inject = [                         'CrmAtcsresponsaveisfinanceirosFormService',
                                                 'CrmAtcstiposdocumentosrequisitantesFormService',
                         '$scope', 'toaster', 'CrmAtcs', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(                         public CrmAtcsresponsaveisfinanceirosFormService: any,
                                                 public CrmAtcstiposdocumentosrequisitantesFormService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
                 this.$scope.$on('crm_atcstiposdocumentosrequisitantes_loaded', () => {
            this.busy = false;
        });
            }
       crmAtcsresponsaveisfinanceirosForm() {
        let modal = this.CrmAtcsresponsaveisfinanceirosFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.responsaveisfinanceiros === undefined ) {
                this.entity.responsaveisfinanceiros = [subentity];
            } else {
                this.entity.responsaveisfinanceiros.push(subentity);
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
    }     crmAtcstiposdocumentosrequisitantesForm() {
        let modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.negociostiposdocumentosrequisitantes === undefined ) {
                this.entity.negociostiposdocumentosrequisitantes = [subentity];
            } else {
                this.entity.negociostiposdocumentosrequisitantes.push(subentity);
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
    }         crmAtcstiposdocumentosrequisitantesFormEdit(subentity: any) {
        let parameter = { 'negocio': subentity.negocio, 'identifier': subentity.negociotipodocumentorequisitante };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open(parameter, subentity);
        modal.result.then(
        (subentity: any) => {
            let key;
            for ( key in this.entity.negociostiposdocumentosrequisitantes ) {
                if ( (this.entity.negociostiposdocumentosrequisitantes[key].negociotipodocumentorequisitante !== undefined && this.entity.negociostiposdocumentosrequisitantes[key].negociotipodocumentorequisitante === subentity.negociotipodocumentorequisitante)
                    || (this.entity.negociostiposdocumentosrequisitantes[key].$id !== undefined && this.entity.negociostiposdocumentosrequisitantes[key].$id === subentity.$id)) {
                    this.entity.negociostiposdocumentosrequisitantes[key] = subentity;
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
    }            uniqueValidation(params: any): any {
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