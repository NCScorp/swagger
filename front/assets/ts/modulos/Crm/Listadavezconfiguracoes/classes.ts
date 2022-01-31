import { IListadavezregrasvaloresApi } from "../Listadavezregrasvalores/classes";
import { IListadavezregrasApi } from "../Listadavezregras/classes";
import { IListadavezVendedor } from "../Listadavezvendedores/classes";

export interface IListadavezconfiguracoesApi {
    listadavezconfiguracao?: string;
    listadavezconfiguracaonovo?: string;
    idpai?: string;
    idpainovo?: string;
    listadavezvendedor?: IListadavezVendedor;
    vendedorfixo?: boolean;
    ordem: number;
    listadavezregra?: IListadavezregrasApi;
    idlistadavezregravalor?: IListadavezregrasvaloresApi;
    idestado?: {
        uf: string;
    };
    idnegociooperacao?: {
        proposta_operacao: string;
    };
    idsegmentoatuacao?: {
        segmentoatuacao: string;
    };
    tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi;
}

export enum EnumListadavezconfiguracoesTipoRegistroApi {
    lvctrRegra = 0,
    lvctrOpcao = 1,
    lvctrFluxoAlternativo = 2
}