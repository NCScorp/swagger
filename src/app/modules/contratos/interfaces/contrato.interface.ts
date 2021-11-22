import { ContratoParticipante } from "./contratoparticipante.interface";
import { IContratoItem } from "../itens/interfaces/contrato-item.interface";
import { ESituacaoFinanceiraContrato } from "./esituacaocontrato.enum";

export interface IContrato {
    id_compartilhado?: string;
    codigo?: string;
    descricao?: string;
    data_registro?: string;
    participante?: ContratoParticipante;
    /**
     * Assumir por enquanto 1: Em dia; 2: Quitado; 3: Vencido.
     */
    situacao_financeira: ESituacaoFinanceiraContrato;
    itens: IContratoItem[];
}