import { EFaturamentoTipo } from "../../../Orcamentos/classes/efaturamentotipo";
import { EServicoTipo } from "../../../Orcamentos/classes/eservicotipo";
import { EDescontoGlobalTipo } from "../../../Fornecedoresenvolvidos/classes/edescontoglobaltipo";

/**
 * Registro que vem da api de dados da ficha financeira
 */
export interface IFichaFinanceiraFornecedor {
    tenant?: string;
    id_grupoempresarial?: string;
    negocio?: string;
    valorapolice?: number;
    fornecedor?: string; 
    permiteorcamentozerado?: boolean;
    fornecedor_nomefantasia?: string;
    fornecedor_nome?: string;
    fornecedor_estabelecimento?: string;
    fornecedor_esperapagamentoseguradora?: boolean;
    fornecedorenvolvido?: string;
    acionamentodata?: string;
    acionamentometodo?: number;
    acionamentorespostaprazo?: number;
    acionamentoaceito?: boolean;
    acionador?: string;
    config_desconto_possuidescontoparcial?: boolean;
    config_desconto_possuidescontoglobal?: boolean;
    config_desconto_descontoglobal?: number;
    config_desconto_descontoglobaltipo?: EDescontoGlobalTipo
    orcamento?: string;
    orcamento_propostaitem?: string;
    orcamento_composicao?: string;
    orcamento_familia?: string;
    orcamento_descricao?: string;
    orcamento_descricaomanual?: boolean;
    orcamento_custo?: number;
    orcamento_valorunitario?: number;
    orcamento_quantidade?: number;
    orcamento_valorreceber?: number;
    orcamento_desconto?: number;
    orcamento_descontoglobal?: number;
    orcamento_faturar?: boolean;
    orcamento_faturamentotipo?: EFaturamentoTipo;
    orcamento_status?: number;
    orcamento_updated_at?: string;
    orcamento_prestador_terceirizado?: string;
    orcamento_prestador_terceirizado_nomefantasia?: string;
    orcamento_prestador_terceirizado_nome?: string;
    orcamento_servicotipo?: EServicoTipo;
}