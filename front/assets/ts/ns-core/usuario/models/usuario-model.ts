
interface IConfiguracao {
    chave: string,
    id_grupoempresarial: string;
    valor: string,
    configuracao: string
    lastupdate: string
    modulo: string
    sistema: string
    tenant: number
  }

export interface Empresa {
    empresa: string;
    codigo: string;
    razaosocial: string;
    raizcnpj?: any;
    ordemcnpj?: any;
    descricao?: any;
    nomecontato?: any;
    lastupdate: string;
}

export interface Grupoempresarial {
    grupoempresarial: string;
    codigo: string;
    descricao: string;
    empresas: Empresa[];
}

export interface Estabelecimento {
    estabelecimento: string;
    codigo: string;
    nomefantasia: string;
    descricao?: any;
    tipoidentificacao: number;
    raizcnpj?: any;
    ordemcnpj?: any;
    cpf?: any;
    cidade?: any;
    site?: any;
    tipologradouro?: any;
    logradouro?: any;
    numero?: any;
    complemento?: any;
    bairro?: any;
    cep?: any;
    dddtel?: any;
    telefone?: any;
    lastupdate: string;
    tenant: number;
    empresa: Empresa;
    municipio?: any;
    grupoempresarial: Grupoempresarial;
}

export interface Organizacao {
    nome: string;
    codigo: string;
    id: number;
    logo: string;
    funcao: string;
    url: string;
    estabelecimento: Estabelecimento[];
    gruposempresariais: Grupoempresarial[];
    configuracoes?: IConfiguracao[];
    permissoes?:string[]

}

export interface Usuario {
    links: string[];
    conta_url: string;
    logout_url: string;
    username: string;
    email: string;
    nome: string;
    organizacoes: Organizacao[];
    roles: string[];
}



