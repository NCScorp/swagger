/**
 * Configuração de geração de documentos
 */
export interface IAtcConfiguracaoDocumento {
    atcconfiguracaodocumento?: string;
    tenant?: number;
    id_grupoempresarial?: string;
    tipogeracaoprestadora?: EnumTipoGeracaoPrestadora;
    tipogeracaoseguradora?: EnumTipoGeracaoSeguradora;
    atcsconfiguracoesdocumentositens?: IAtcConfiguracaoDocumentoItem[];
    atcsconfiguracoestemplatesemails?: IAtcConfiguracaoTemplateEmail[];
    emailpadrao?: string;
}

/**
 * Representa os relatórios(documentos pdf) gerados pelo sistema
 */
export interface IDocumentoFop {
    documentofop?: string;
    codigodocumento?: string;
    nomedocumento?: string;
}

/**
 * Documentos do atendimento configurados para serem gerados.
 */
export interface IAtcConfiguracaoDocumentoItem {
    atcconfiguracaodocumentoitem?: string;
    tenant?: number;
    id_grupoempresarial?: string;
    documentofop?: IDocumentoFop;
    tipo?: EnumTipoDocumentoItem;
}

/**
 * Templates de e-mail para geração de documento
 */
export interface IAtcConfiguracaoTemplateEmail {
    atcconfiguracaotemplateemail?: string;
    nome?: string;
    codigo?: string;
}

/**
 * Enum do Tipo de documento configurado para ser gerado.
 */
export enum EnumTipoDocumentoItem {
    tdiPrestadora = 1,
    tdiSeguradora = 2,
    tdiRPS = 3,
}

/**
 * Enum de definicao do tipo de geração dos documentos para a prestadora
 */
export enum EnumTipoGeracaoPrestadora {
    tgpGerarUmPorPrestador = "1",
    tgpNaoGerar = "2"
}

/**
 * Enum de definicao do tipo de geração dos documentos para a seguradora
 */
export enum EnumTipoGeracaoSeguradora {
    tgpGerarUmPorPrestador = "1",
    tgpNaoGerar = "2"
}