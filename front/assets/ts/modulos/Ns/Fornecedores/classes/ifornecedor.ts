import { IEstabelecimento } from "../../Estabelecimentos/classes/iestabelecimento";

export interface IFornecedor {
    fornecedor?: string;
    formapagamento:any,
    forma_pagamento:any,
    estabelecimentoid?: IEstabelecimento;
    razaosocial?: string;
    nomefantasia?: string;
    esperapagamentoseguradora?: boolean;
}