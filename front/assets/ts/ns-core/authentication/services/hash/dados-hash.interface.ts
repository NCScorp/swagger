export interface IDadosHash {
    nome: string;
    email: string;
    permissoes: string[];
    entidades: any;
    grupoempresarial: {
        grupoempresarial: string;
        codigo: string;
    }
    tenant: {
        tenant: number;
        codigo: string;
    }
    iat: number;
    nbf: number;
    exp: number;
}