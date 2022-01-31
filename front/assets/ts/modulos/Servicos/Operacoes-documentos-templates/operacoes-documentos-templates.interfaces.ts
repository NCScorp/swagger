import { IDocumentoFOPTemplate } from "../../Ns/Documentos-fop-templates/documentos-fop-templates.interfaces";

export interface IOperacoesDocumentosTemplate {
    operacaodocumentotemplate?: any;
    tenant: number;
    operacaoordemservico?: any;
    documentofoptemplate: IDocumentoFOPTemplate;
    padrao: boolean;
  }