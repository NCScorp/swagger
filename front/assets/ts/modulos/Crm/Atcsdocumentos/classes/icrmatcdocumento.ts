import { INsTipoDocumento } from "../../../Ns/Tiposdocumentos/classes/instipodocumento";
import { IUser } from "../../../Commons/classes/iuser";
import { ECrmAtcDocumentoSituacao } from "./ecrmatcdocumentosituacao";
import { ECrmAtcDocumentoTipoNotaFiscal } from "./ecrmatcdocumentostiponf";
import { IOrcamento } from "../../Orcamentos/classes/iorcamento";
import { IAtc } from "../../Atcs/classes/iatc";

export interface ICrmAtcDocumento {
    negocio?: IAtc;
    negociodocumento?: string;
    tipodocumento?: INsTipoDocumento;
    url?: string;
    tipomime?: string;
    created_at?: string;
    created_by?: IUser;
    status?: ECrmAtcDocumentoSituacao;
    descricao?: string;
    seriedocumento?: string;
    numerodocumento?: string;
    emissaodocumento?: string;
    tiponf?: ECrmAtcDocumentoTipoNotaFiscal;
    orcamentos?: IOrcamento[];
}