export interface ICliente {
    cliente: string;
    razaosocial: string;
    nomefantasiacompleto: string;
    nomefantasia: string;
    cnpj: string;
    cpf?: any;
    email?: any;
    status_suporte: string;
    diasparavencimento?: any;
    anotacao?: any;
    codigo: string;
    tipo: number;
    bloqueado: boolean;
    formapagamento: string;
    vendedor?: any;
    conta: string;
  }