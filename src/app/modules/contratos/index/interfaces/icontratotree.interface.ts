import { ESituacaoFinanceiraContrato } from "@modules/contratos/interfaces/esituacaocontrato.enum";
import { IContratoItemTree } from "./icontratoitemtree.interface";
import { IDadosTree } from "./idadostree.interface";

export interface IContratoTree extends IDadosTree{
    contrato: string;
    situacao_financeira?: ESituacaoFinanceiraContrato;
    itens: IContratoItemTree[];
}