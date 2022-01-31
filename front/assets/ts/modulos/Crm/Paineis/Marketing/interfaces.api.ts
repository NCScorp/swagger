export interface IPainelMarketingApi {
    meses: IPainelMarketingMesApi[];
    classificadores: IPainelMarketingClassificadorApi[];
}

export interface IPainelMarketingMesApi {
    anomes: string;
    negociostotais: number;
    listaqualificacao: IPainelMarketingMesQualificacaoApi[];
}

export interface IPainelMarketingMesQualificacaoApi {
    anomes: string;
    negociosqualificados: number;
    negociosdesqualificados: number;
}

export interface IPainelMarketingClassificadorApi {
    entidade: EnumPainelMarketingClassificadorEntidadeApi;
    id: string | number | boolean;
    nome: string;
    prenegocios: string[];
    negociosqualificados: string[];
    negociosdesqualificados: string[];
}

export enum EnumPainelMarketingClassificadorEntidadeApi {
    pmceCampanhaorigem = 0,
    pmceMidiaorigem = 1,
    pmceTipoacionamento = 2,
    pmceEstabelecimento = 3,
    pmceAreanegocio = 4,
    pmceAtribuicao = 5,
    pmceSegmentoatuacao = 6,
    pmceFaturamentoanual = 7,
    pmceEhcliente = 8,
    pmceCargo = 9,
    pmceQualificadoem = 10,
    pmceMotivodesqualificacao = 11
}