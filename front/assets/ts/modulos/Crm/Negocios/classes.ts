export interface INegocioListaApi {
    /**
     * Guid de identificação do negócio
     */
    documento?: string;
    tenant?: number;
    id_grupoempresarial?: string;
    operação?: INegocioOperacao;
    numero?: string;
    estabelecimento?: IEstabelecimento;
    cliente?: ICliente;
    contrato?: IContrato;
    codigodepromocao?: IPromocaoLead;
    midiadeorigem?: IMidiaOrigem;
    cliente_codigo?: string;
    cliente_companhia?: string;
    cliente_nomefantasia?: string;
    cliente_qualificacao?: EnumClienteQualificacao;
    cliente_documento?: string;
    cliente_email?: string;
    cliente_site?: string;
    clientecaptador?: IPessoa;
    cliente_segmentodeatuacao?: ISegmentoAtuacao;
    clientereceitaanual?: EnumClienteReceitaAnual;
    uf?: string;
    segmentodeatuacao?: ISegmentoAtuacao;
    created_at?: string;
    created_by?: IUsuario;
    updated_at?: string;
    updated_by?: IUsuario;
    situacaoprenegocio?: ISituacaoPreNegocio;
    prenegocio?:  0 | 1;
    ehcliente?: 0 | 1;
    observacao?: string;
    clientemunicipioibge?: string;
    datahoraultimofollow?: string;
    negocioscontatos?: INegocioContato[];
    tipoqualificacaopn?: EnumTipoQualificacao;
    /**
     * Não mapeado
     */
    tiponegocio?: EnumTipoNegocio;
    buscoucliente?: boolean;
    created_at_formatado?: string;
    datahoraultimofollow_formatado?: string;
}

export interface INegocioApi extends INegocioListaApi {

}

export interface INegocioOperacao {}

export interface IEstabelecimento {}

export interface ICliente {
    cliente?: string;
    codigo?: string;
    razaosocial?: string;
    nomefantasia?: string;
}

export interface IContrato {}

export interface IPromocaoLead {}

export interface IMidiaOrigem {}

export interface IPessoa {}

export interface ISegmentoAtuacao {}

export interface IUsuario {
    nome: string;
    email: string;
}

export interface ISituacaoPreNegocio {
    situacaoprenegocio?: string;
    nome?: string;
}

export interface INegocioContato extends IListaCadastro{
    /**
     * Guid de identificação do contato
     */
    id?: string;
    tenant?: number;
    id_grupoempresarial?: string;
    /**
     * Verificar depois
     */
    negocio?: any;
    nome?: string;
    sobrenome?: string;
    cpf?: string;
    cargo?: EnumNegocioContatoCargo;
    sexo?: string;
    email?: string;
    nascimento?: string;
    principal?: boolean;
    responsavellegal?: boolean;
    ddi?: string;
    ddd?: string;
    telefone?: string;
    ramal?: string;
    titulo?: string;
    created_at?: string;
    created_by?: IUsuario;
    updated_at?: string;
    updated_by?: IUsuario;
}

export enum EnumClienteReceitaAnual {
    craAte5Milhoes      = 5000000,
    craAte30Milhoes     = 30000000,
    craAte100Milhoes    = 100000000,
    craAte300Milhoes    = 300000000,
    craAte500Milhoes    = 500000000,
    craAte1Bilhao       = 1000000000,
    craMaisDe1Bilhao    = 2000000000
}

export enum EnumTipoNegocio {
    tnPreNegocio = 0,
    tnNegocio = 1
}

export enum EnumClienteQualificacao {
    cqCNPJ = 0,
    cqCPF = 1
}

export enum EnumNegocioContatoCargo {
    nccSocioProprietarioCEO = "Sócio/Proprietário/CEO",
    nccDiretor = "Diretor",
    nccConsultorController = "Consultor/Controller",
    nccGerenteCoordenadorAdmFinanc = "Gerente/Coordenador Adm./Financ.",
    nccGerenteCoordenadorTI = "Gerente/Coordenador de TI",
    nccGerenteCoordenadorOutrasAreas = "Gerente/Coordenador de Outras Áreas",
    nccAnalista = "Analista",
    nccEstagiario = "Estagiário"
}

export enum EnumTipoQualificacao {
    tqSemQualificacao = 0,
    tqQualificado = 1,
    tqDesqualificado = 2
}

export interface IFollowUpNegocio {
    /**
     * Guid de identificação do follow up
     */
    id: string;
    tenant: number;
    proposta: INegocioApi;
    participante: ICliente;
    historico: string;
    receptor: string;
    meiocomunicacao: string;
    figuracontato: string;
    created_at: string;
    created_by: IUsuario;
}

export interface IListaCadastro {
    "$$hashKey"?: string;
}