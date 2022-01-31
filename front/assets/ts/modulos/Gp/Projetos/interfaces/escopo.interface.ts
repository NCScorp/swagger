import { IOrdemServico } from "assets/ts/modulos/Servicos/Ordens-servicos/ordem-servico.interfaces";
import { EtapasEnum } from "../../Etapas/enum/etapas.enum";
import { IEtapas } from "../../Etapas/models/etapas.model";
import { tipoItemEnum } from "../enums/tipoItem.enum";
import { IProjeto } from "./projetos.interface";

export interface IEscopo {
  projetoitemescopo: string;
  projetoitemescopopai: string;
  tipoitem: tipoItemEnum;
  tipoetapa: EtapasEnum;
  datahorainicio: string | Date;
  datahorafim: string | Date;
  numero: string;
  descricao: string;
  situacao: string | number;
  veiculo;
  ordemservicotemplate: string;
  responsavelos: { tecnico: ITecnico };
  ordemservico: IOrdemServico;
  enderecoorigem: IEndereco;
  enderecodestino: IEndereco;
  etapa: IEtapas;
  projeto: Partial<IProjeto>;
}

export interface IEndereco {
  endereco: string;
  nome: string;
}
export interface ITecnico {
  nome: string;
  contanasajon: string;
  tecnico: string;
  cpf: string;
}