import { ITemplateOrdemServicoMaterial } from "../interfaces/templates-os-material.interface";
import { TemplateOrdemServicoMaterialFormView } from "../interfaces/template-os-material.form";

export interface TemplatesOrdemServicoMaterialModalControllerRetorno {
    entity: TemplateOrdemServicoMaterialFormView
}

export class TemplatesOrdemServicoMaterialModalController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        'constructors'
    ];

    public action: string = 'update';
    public form: any;
    public submitted: boolean = false;

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: TemplateOrdemServicoMaterialFormView,
        public constructors: any,
    ) {
        if (this.entity == null) {
            this.action = 'insert';
            
            // Crio entity
            this.entity = new TemplateOrdemServicoMaterialFormView(<ITemplateOrdemServicoMaterial>{
                ordemservicotemplate: this.constructors.ordemservicotemplate
            });
        }
    }

    submit() {
        this.submitted = true;
        if (this.form.$valid) {
            const retorno: TemplatesOrdemServicoMaterialModalControllerRetorno = {
                entity: this.entity
            }
            this.$uibModalInstance.close(retorno);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}
