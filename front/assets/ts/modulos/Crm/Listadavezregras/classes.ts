import { IListadavezregrasvaloresApi } from "../Listadavezregrasvalores/classes";

export interface IListadavezregrasApi {
    listadavezregra: string;
    nome?: string;
    totalvalores?: number;
    tipoentidade?: EnumListadavezregrasTipoEntidadeApi;
    itens?: IListadavezregrasvaloresApi[];
}

export enum EnumListadavezregrasTipoEntidadeApi {
    lvrteValorFixo = 0,
    lvrteEstado = 1,
    lvrteNegocioOperacao = 2,
    lvrteSegmentoAtuacao = 3
}