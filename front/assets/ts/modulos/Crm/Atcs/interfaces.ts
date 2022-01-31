export interface IResponsavelFinanceiro {
    principal?: boolean;
    responsavelfinanceiro?: ICliente;
}
export interface ICliente {
    cliente?: string;
    nomefantasia?: string;
    formapagamentoguid: string;
}
export interface IResponsabilidadefinanceira {
    responsabilidadesfinanceirasvalores: IResponsabilidadefinanceiravalor[];
    propostaitem: string;
    propostaitemfamilia: string;
    propostaitemfuncao: string;
    geranotafiscal: boolean;
    orcamento: string;
}
export interface IResponsabilidadefinanceiravalor {
    responsavelfinanceiro: string;
    valorpagar: number;
    contrato: string;
}
export interface IPropostaItem {
    propostaitem?: string;
    nome?: string;
    possuifamilias?: boolean;
    possuifuncoes?: boolean;
    servicoorcamento?: IOrcamento;
    id_apolice?: string;
    composicao?: IComposicao;
}

export interface IComposicao {
    composicao?: string;
    codigo?: string;
    nome?: string;
}
export interface IPropostasItemFamilia {
    propostaitemfamilia?: string;
    propostaitem?: string;
    valor?: number;
    quantidade?: number;
    familia?: IFamilia;
}
export interface IFamilia {
    descricao: string;
}
export interface IPropostasItemFuncao {
    propostaitemfuncao?: string;
    propostaitem?: string;
    quantidade?: number;
    funcao?: IFuncao;
}
export interface IFuncao {
    descricao: string;
}
export interface IOrcamento {
    orcamento?: string;
    propostaitem?: IPropostaItem;
    propostaitemfamilia?: IPropostasItemFamilia;
    propostaitemfuncao?: IPropostasItemFuncao;
    valor: number;
    faturamentotipo: number;
    execucaoservico: boolean;
    execucaodeservico: boolean;
    descricao: string;
    familia: any,
    composicao: any,
    status: number
}