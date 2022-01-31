import { EtapasEnum } from "../enum/etapas.enum";

export interface IEtapas {
  nome: string;
  tipo: EtapasEnum;
  projetoetapa: string;
}