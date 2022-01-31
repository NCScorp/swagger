import angular = require('angular');

import { TemplateOrdemServicoMaterialFormView } from '../interfaces/template-os-material.form';

export class TemplatesOrdemServicoMaterialFormController {

    static $inject = [
        '$scope', 
        'toaster'
    ];

    public entity: TemplateOrdemServicoMaterialFormView;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any
    ) {}

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
    }

    submit() {
        this.form.$submitted = true;

        if (this.form.$valid) {
            return ;
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
}