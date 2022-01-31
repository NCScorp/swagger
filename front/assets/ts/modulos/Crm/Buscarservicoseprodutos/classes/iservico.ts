import { EServicoProduto } from './eservicoproduto';

export interface IServico {
    composicao?: string;
    codigo?: string;
    nome?: string;
    tipo?: EServicoProduto;
    servicocoringa?: boolean;
}