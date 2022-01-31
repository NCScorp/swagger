import angular = require('angular');
import { TemplateOrdemServicoFormView } from '../interfaces/template-os.form';

export class TemplatesOrdemServicoFormController {

    static $inject = [
        '$scope'
    ];

    public entity: TemplateOrdemServicoFormView;
    public form: any;

    constructor(
        public $scope: angular.IScope
    ) {}

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
    }
}