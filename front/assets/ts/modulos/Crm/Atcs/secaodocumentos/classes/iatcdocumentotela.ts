import { CrmAtcTipoDocumentoRequisitante } from "../../../Atcstiposdocumentosrequisitantes/classes/crmatctipodocumentorequisitante";
import { CrmAtcDocumento } from "../../../Atcsdocumentos/classes/crmatcdocumento";

export interface IAtcDocumentoTela {
    /**
     * Tipo de Documento requisitado pelo atendimento
     */
    atctipodocumentorequisitante: CrmAtcTipoDocumentoRequisitante;
    /**
     * Documentos enviados no atendimento para o tipo de documento requisitado
     */
    atcsdocumentos: CrmAtcDocumento[];
    /**
     * Informação preenchida pelo object list
     */
    $$id?: number;
    /**
     * Informação preenchida pelo object list
     */
    $$saved?: boolean;
}