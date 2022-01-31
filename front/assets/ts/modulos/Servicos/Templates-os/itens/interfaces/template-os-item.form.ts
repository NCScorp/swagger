import { IServicoTecnico } from "../../../Servicos-tecnicos/interfaces/servicos-tecnicos.interface"
import { TFormView } from "../../../../../shared/form/tform-view";
import { TemplateOrdemServicoItemChecklistFormView } from "../checklist/interfaces/template-os-item-checklist.form";
import { ITemplateOrdemServicoItem } from "./templates-os-item.interface";

export class TemplateOrdemServicoItemFormView extends TFormView<ITemplateOrdemServicoItem> implements ITemplateOrdemServicoItem{
    // Propriedades da interface de api
    ordemservicotemplate: string;
    ordemservicotemplateitem: string;
    quantidade: number;
    valor: number;
    servicotecnico: IServicoTecnico;

    /**
     * Id utilizado enquanto registro não possui chave primaria
     */
    idVirtual: number = 0;
    /**
     * Checklist do serviço
     */
    arrChecklist: TemplateOrdemServicoItemChecklistFormView[] = [];
    /**
     * Checklist do serviço: Itens para exclusão
     */
    arrChecklistExcluir: TemplateOrdemServicoItemChecklistFormView[] = [];
    /**
     * Controle se já tentou carregar o checklist com sucesso, mesmo que não possua nenhum item na lista
     */
    checklistCarregado: boolean = false;

    constructor(
        dados: ITemplateOrdemServicoItem
    ){
        super(dados);

        if (!this.quantidade) {
            this.quantidade = 1;
        }

        if (!this.valor) {
            this.valor = 0;
        }
    }

    /**
     * Realiza clonagem do objeto
     * @returns
     */
    clone(): TemplateOrdemServicoItemFormView {
        const i = this.toInterface();
        const obj = new TemplateOrdemServicoItemFormView(i);

        obj.idVirtual = (<any>i).idVirtual;
        // Clono lista de checklist
        obj.arrChecklist = this.arrChecklist.map(item => item.clone());
        obj.arrChecklistExcluir = this.arrChecklistExcluir.map(item => item.clone());
        obj.checklistCarregado = this.checklistCarregado;

        return obj;
    }
}