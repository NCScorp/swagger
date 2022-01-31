export interface IOperacaoOS {
    operacaoordemservico: string;
}
export interface IEstabelecimento {
    estabelecimento: string;
    nomefantasia?: string;
}

export interface ITemplateOrdemServico {
    ordemservicotemplate: string;
    nome: string;
    estabelecimento: IEstabelecimento;
    operacao: IOperacaoOS;
}