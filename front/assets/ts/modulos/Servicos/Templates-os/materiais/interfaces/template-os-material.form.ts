import { EstoqueItem } from "assets/ts/modulos/Estoque/Estoque-itens/interfaces/estoque-item.interface";
import { TFormView } from "../../../../../shared/form/tform-view";
import { ITemplateOrdemServicoMaterial } from "./templates-os-material.interface";

export class TemplateOrdemServicoMaterialFormView extends TFormView<ITemplateOrdemServicoMaterial> implements ITemplateOrdemServicoMaterial{
    // Propriedades da interface de api
    ordemservicotemplate: string;
    ordemservicotemplatematerial: string;
    quantidade: number;
    item: EstoqueItem;

    /**
     * Id utilizado enquanto registro não possui chave primaria
     */
    idVirtual: number = 0;

    constructor(
        dados: ITemplateOrdemServicoMaterial
    ){
        super(dados);

        if (!this.quantidade) {
            this.quantidade = 1;
        }
    }

    /**
     * Realiza clonagem do objeto
     * @returns
     */
    clone(): TemplateOrdemServicoMaterialFormView {
        const i = this.toInterface();
        const obj = new TemplateOrdemServicoMaterialFormView(i);

        obj.idVirtual = (<any>i).idVirtual;

        return obj;
    }
}