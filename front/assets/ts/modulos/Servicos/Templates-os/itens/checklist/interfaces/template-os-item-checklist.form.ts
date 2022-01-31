import { TFormView } from "../../../../../../shared/form/tform-view";
import { ITemplateOrdemServicoItemChecklist } from "./templates-os-item-checklist.interface";

export class TemplateOrdemServicoItemChecklistFormView extends TFormView<ITemplateOrdemServicoItemChecklist> implements ITemplateOrdemServicoItemChecklist{
    // Propriedades da interface de api
    ordemservicotemplatechecklist: string;
    ordemservicotemplate: string;
    ordemservicotemplateitem: string;
    descricao: string;

    /**
     * Id utilizado enquanto registro não possui chave primaria
     */
    idVirtual: number = 0;

    clone(): TemplateOrdemServicoItemChecklistFormView {
        const i = this.toInterface();
        const obj = new TemplateOrdemServicoItemChecklistFormView(i);

        obj.idVirtual = (<any>i).idVirtual;
        
        return obj;
    }
}