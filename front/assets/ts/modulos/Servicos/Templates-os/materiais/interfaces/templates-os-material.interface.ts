import { EstoqueItem } from "assets/ts/modulos/Estoque/Estoque-itens/interfaces/estoque-item.interface";

export interface ITemplateOrdemServicoMaterial {
    ordemservicotemplate: string;
    ordemservicotemplatematerial: string;
    quantidade: number;
    item: EstoqueItem;
}