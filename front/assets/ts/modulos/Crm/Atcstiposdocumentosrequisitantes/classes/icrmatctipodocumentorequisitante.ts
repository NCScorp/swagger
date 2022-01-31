import { INsTipoDocumento } from "../../../Ns/Tiposdocumentos/classes/instipodocumento";
import { IUser } from "../../../Commons/classes/iuser";
import { IFornecedor } from "../../../Ns/Fornecedores/classes/ifornecedor";
import { INsCliente } from "../../../Ns/Clientes/classes/inscliente";
import { ICrmTemplateProposta } from "../../Templatespropostas/classes/icrmtemplateproposta";

export interface ICrmAtcTipoDocumentoRequisitante {
    negociotipodocumentorequisitante: string;
    tipodocumento?: INsTipoDocumento;
    copiasimples?: boolean;
    copiaautenticada?: boolean;
    original?: boolean;
    permiteenvioemail?: boolean;
    requisitantenegocio: boolean;
    pedirinformacoesadicionais?: boolean;
    naoexibiremrelatorios?: boolean;
    requisitantecliente: INsCliente;
    requisitantefornecedor: IFornecedor;
    requisitanteapolice: ICrmTemplateProposta;
    created_by?: IUser;
    created_at?: string;
}