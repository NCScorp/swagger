import { IFornecedor } from '../../../Ns/Fornecedores/classes/ifornecedor';
import { EFaturamentoTipo } from './efaturamentotipo';
import { EServicoTipo } from './eservicotipo';

/**
 * Interface utilizada nas APIs de orçamento
 */
export interface IOrcamento {
    orcamento?: string;
    atc?: string;
    propostaitem?: string;
    proposta?: string;
    /**
     * Mercadoria atrelada ao orçamento
     */
    familia?: string;
    /**
     * Serviço atrelado ao orçamento
     */
    composicao?: string;
    /**
     * Define se a descrição pode ser alterada.
     */
    descricaomanual?: boolean;
    /**
     * Descrição do orçamento. Quando descricaomanual for falso, contém o nome da família ou composição.
     */
    descricao?: string;
    custo?: string;
    quantidade?: number;
    valorunitario?: string;
    /**
     * Orçamento: quantidade x valorunitario
     */
    valor?: string;
    /**
     * Valor autorizado do orçamento
     */
    valorreceber?: string;
    desconto?: string;
    descontoglobal?: string;
    /**
     * Situação do orçamento: 0 = aberto; 1= enviado; 2 = aprovado; 3 = recusado
     */
    status?: number;
    faturar?: boolean;
    faturamentotipo?: EFaturamentoTipo;
    servicotipo?: EServicoTipo;
    fornecedor?: IFornecedor;
    /**
     * Id do prestador de serviços terceirizado Ns/Fornecedores
     */
    fornecedorterceirizado?: string;
    // Nome e nome fantasia do prestador terceirizado não fazem parte de orçamentos, são nao mapeados
    fornecedorterceirizado_nome?: string;
    fornecedorterceirizado_nomefantasia?: string;
    updated_at?: string;
    // Campos utilizados para definir ao calcular descontos
    descontoglobalunitario?: string;
    descontoglobalresto?: string;
    descontoglobalrestoorcamento?: string;
    /**
     * Utilizado para validar formulário sem passar todas as informações
     */
    xmlauxiliar?: boolean;
}