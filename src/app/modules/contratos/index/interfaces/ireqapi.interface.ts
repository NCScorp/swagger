import { IGetAllFilters } from "@modules/contratos/contratos.service";
import { IContratoApi } from "@modules/contratos/interfaces/icontratoapi.interface";

export interface IRequisicaoApi {
    retornoApi: IContratoApi;
    filtros: IGetAllFilters;
}