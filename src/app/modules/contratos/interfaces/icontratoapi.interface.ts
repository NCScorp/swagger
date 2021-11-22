export interface IContratoApi {
    count: number;
    next: string;
    previous: string;
    items: IContratoListaContratoApi[];
}

export interface IContratoListaContratoApi {
    id_compartilhado: string;
    codigo: string;
    descricao: string;
    data_registro: string;
    participante: IContratoListaContratoParticipanteApi;
    itens: IContratoListaContratoItemApi[];
}

export interface IContratoListaContratoParticipanteApi {
    id_compartilhado: string;
    cpf_cnpj: string;
    razao_social: string;
    nome_fantasia: string;
}

export interface IContratoListaContratoItemApi {
    id: string;
    valor_unitario: number;
    quantidade: number;
    codigo_item_contrato: string;
    descricao: string;
}