import angular = require('angular');
import { TemplateOrdemServicoFormView } from '../interfaces/template-os.form';
import { TemplatesOrdemServicoService } from '../templates-os.service';

export class TemplatesOrdemServicoShowFormController {

    static $inject = [
        '$scope', 
        'toaster', 
        'templatesOrdemServicoService', 
        'utilService'
    ];

    public entity: TemplateOrdemServicoFormView;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(
        public $scope: angular.IScope,
        public toaster: any,
        public templatesOrdemServicoService: TemplatesOrdemServicoService,
        public utilService: any
    ) {
    }

    $onInit() {
    }
}
