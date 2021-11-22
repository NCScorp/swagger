import { IContratoTree } from "./icontratotree.interface";
import { IDadosTree } from "./idadostree.interface";

export interface IClienteTree extends IDadosTree{
    cliente: string;
    contratos: IContratoTree[];
}