import angular = require('angular');
import { TecnicosService } from '../tecnicos.service';

export class TecnicosShowFormController {

    static $inject = ['$scope', 'toaster', 'tecnicosService', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(
        public $scope: angular.IScope,
        public toaster: any,
        public tecnicosService: TecnicosService,
        public utilService: any
    ) {
    }
    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
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