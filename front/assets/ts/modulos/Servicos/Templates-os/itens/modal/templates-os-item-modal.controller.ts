import { TemplateOrdemServicoItemFormView } from "../interfaces/template-os-item.form";
import { ITemplateOrdemServicoItem } from "../interfaces/templates-os-item.interface";

export interface TemplatesOrdemServicoItemModalControllerRetorno {
    entity: TemplateOrdemServicoItemFormView
}

export class TemplatesOrdemServicoItemModalController {
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
        public entity: TemplateOrdemServicoItemFormView,
        public constructors: any,
    ) {
        if (this.entity == null) {
            this.action = 'insert';
            
            // Crio entity
            this.entity = new TemplateOrdemServicoItemFormView(<ITemplateOrdemServicoItem>{
                ordemservicotemplate: this.constructors.ordemservicotemplate
            });
        }
    }

    submit() {
        this.submitted = true;
        if (this.form.$valid) {
            const retorno: TemplatesOrdemServicoItemModalControllerRetorno = {
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
