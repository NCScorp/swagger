import angular = require('angular');
export class CrmListadavezregrasDefaultShowController {

    static $inject = [                         'CrmListadavezregrasvaloresFormService',
                                                'CrmListadavezregrasvaloresFormShowService',
                         '$scope', 'toaster', 'CrmListadavezregras', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(                         public CrmListadavezregrasvaloresFormService: any,
                                                public CrmListadavezregrasvaloresFormShowService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
            }
               crmListadavezregrasvaloresFormShow(subentity: any) {
        let parameter = { 'listadavezregra': subentity.listadavezregra, 'identifier': subentity.listadavezregravalor };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.CrmListadavezregrasvaloresFormShowService.open(parameter, subentity);
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
