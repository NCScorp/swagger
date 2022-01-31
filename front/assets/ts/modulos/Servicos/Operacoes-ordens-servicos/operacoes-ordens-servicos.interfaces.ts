import { IOperacoesDocumentosTemplate } from '../Operacoes-documentos-templates/operacoes-documentos-templates.interfaces';
import { ICreatedBy } from '../Ordens-servicos/ordem-servico.interfaces';

export interface IOperacaoOrdemServico {
  operacaoordemservico: string;
  codigo: string;
  descricao: string;
  contrato: boolean;
  ativo: boolean;
  operacaoativa: boolean;
  centrodecusto: boolean;
  visita: boolean;
  materiais: boolean;
  encerraraoexecutarvisita: boolean;
  utilizatipomanutencao: boolean;
  projeto: boolean;
  ordemdeservico: boolean;
  atendimento: boolean;
  veiculo: boolean;
  faturamento: boolean;
  tenant: number;
  created_at: string;
  created_by: ICreatedBy;
  updated_at?: any;
  updated_by?: any;
  tipoordemservico?: any;
  tipomanutencao?: any;
  operacoesdocumentostemplates: IOperacoesDocumentosTemplate[];
}
