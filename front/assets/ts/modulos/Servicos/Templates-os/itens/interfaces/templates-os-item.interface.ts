import { IServicoTecnico } from "../../../Servicos-tecnicos/interfaces/servicos-tecnicos.interface";

export interface ITemplateOrdemServicoItem {
    ordemservicotemplateitem?: string;
    ordemservicotemplate: string;
    quantidade?: number;
    valor?: number;
    servicotecnico?: IServicoTecnico;
}