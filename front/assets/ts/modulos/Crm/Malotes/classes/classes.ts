export interface IMaloteApi {
    malote?: string;
    documentos?: IMaloteDocumentoApi[];
    requisitantecliente?: IClienteApi;
    status?: EnumMaloteStatus;
}

export interface IMaloteDocumentoApi {
    malotedocumento?: string;
    malote?: string;
    documento?: IAtcDocumentoApi;
    tenant?: number;
}

export interface IAtcDocumentoApi {
    negociodocumento?: string;
    negocio?: string;
    tipodocumento?: string;
    status?: EnumAtcDocumentoStatus
    url?: string;
    descricao?: string;
    tenant?: number;
    id_grupoempresarial?: string;
    negocio_nome?: string;
    tipodocumento_nome?: string;
    created_at?: string;
}

export interface IClienteApi {
    cliente?: string;
}

/**
* Enumerado das situações do Negócio Documento
*/
export enum EnumAtcDocumentoStatus {
   ndsPendente = 0,
   ndsRecebido = 1,
   ndsPreAprovado = 2,
   ndsEnviadoparaRequisitante = 3,
   ndsAprovado = 4,
   ndsRecusado = 5
}

/**
* Enumerado das situações do Malote
*/
export enum EnumMaloteStatus {
    msNovo = 0,
    msEnviado = 1,
    msAceito = 2,
    msAceitoParcialmente = 3,
    msRecusado = 4,
    msFechado = 5
 }