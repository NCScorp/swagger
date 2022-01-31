import { IFornecedor } from "../../../Ns/Fornecedores/classes/ifornecedor";
import { IOrcamento } from "../../Orcamentos/classes/iorcamento";
import { IUser } from "../../../Commons/classes/iuser";
import { EDescontoGlobalTipo } from "./edescontoglobaltipo";

export interface IFornecedorenvolvido {
    fornecedorenvolvido?: string;
    fornecedor?: IFornecedor;
    proposta?: string;
    acionamentodata?: string;
    acionamentometodo?: number;
    acionamentorespostaprazo?: number;
    acionamentoaceito?: boolean;
    /**
     * Só aparece quando o fornecedor é acionado(criação de fornecedor envolvido)
     */
    orcamentos?: IOrcamento[];
    updated_by?: IUser;
    possuidescontoparcial?: boolean;
    possuidescontoglobal?: boolean;
    descontoglobal?: number;
    descontoglobaltipo?: EDescontoGlobalTipo

}