import { SituacaoEnum } from "../enums/situacaoEnum";
export interface IProjeto {
  atc: string;
  codigo: string;
  datafim: string;    // yyyy-mm-dd
  datainicio: string; // yyyy-mm-dd
  projeto: string;
  nome: string;
  situacao: SituacaoEnum;
  tempoprevisto: number;
  responsavel_conta_nasajon;
  dadosAtc,
  camposCustomizadosDadosResponsavel,
  template?,
  $$__submitting: boolean
}

interface IAtendimentoComercial {
  
}

interface IAtendimentoComercial {
  
}
