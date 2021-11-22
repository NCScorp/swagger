import { EConfigRequisicaoTipo } from "./config-requisicao-tipo.enum";

export interface IConfigRequisicao {
    tipo: EConfigRequisicaoTipo;
    /**
     * Deve ser utilizado para requisições customizadas por service
     */
    tipoCustomizado?: any;
    nomeRota: string;
    eventoSucesso?: string;
    eventoFalha?: string;
}