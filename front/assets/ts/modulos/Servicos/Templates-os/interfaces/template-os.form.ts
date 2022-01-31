import { TFormView } from "../../../../shared/form/tform-view";
import { TemplateOrdemServicoItemFormView } from "../itens/interfaces/template-os-item.form";
import { TemplateOrdemServicoMaterialFormView } from "../materiais/interfaces/template-os-material.form";
import { IEstabelecimento, IOperacaoOS, ITemplateOrdemServico } from "./templates-os.interface";

export class TemplateOrdemServicoFormView extends TFormView<ITemplateOrdemServico> implements ITemplateOrdemServico{
    // Propriedades da interface de api
    ordemservicotemplate: string;
    nome: string;
    estabelecimento: IEstabelecimento;
    operacao: IOperacaoOS;

    /**
     * Lista de serviços do template
    */
    arrItens: TemplateOrdemServicoItemFormView[] = [];

    /**
     * Lista de serviços do template para exclusão
    */
    arrItensExcluir: TemplateOrdemServicoItemFormView[] = [];

    /**
     * Lista de materiais do template
    */
    arrMateriais: TemplateOrdemServicoMaterialFormView[] = [];
    
    /**
     * Lista de materiais do template par exclusão
    */
    arrMateriaisExcluir: TemplateOrdemServicoMaterialFormView[] = [];

    /**
     * Controle se já tentou carregar os serviços com sucesso, mesmo que não possua nenhum item na lista
    */
    servicosCarregado: boolean = false;
    
    /**
     * Controle se já tentou carregar os materiais com sucesso, mesmo que não possua nenhum item na lista]
    */
    materiaisCarregado: boolean = false;
}