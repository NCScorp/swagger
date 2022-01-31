import { IFornecedor } from '../../../../Ns/Fornecedores/classes/ifornecedor';
import { ICrmItemProcessarContaPagar } from './icrmitensprocessarcontaspagar';
import { IConta } from '../../../../Financas/Contas/classes/contas.interfaces';

export interface ICrmAtcContaPagar {
  atccontaapagar: string;
  atc: string;
  servico: string;
  orcamento: string;
  itemprocessarcontapagar: ICrmItemProcessarContaPagar;
  descricao: string;
  quantidade: number;
  valoracordado: string;
  valorpagar: string;
  negociodocumento: string;
  numerodocumento: string;
  datadocumento: string;
  prestador: IFornecedor;
  contaemprestimo?: IConta;
}
