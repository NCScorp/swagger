import { ECrmAtcContaPagarTipoProcessamentoAcao } from "./ecrmatccontapagartipoprocessamentoacao";
import { ECrmAtcContaPagarTipoProcessamento } from "./ecrmatccontapagartipoprocessamento";

export interface ICrmItemProcessarContaPagar {
    itemprocessarcontapagar: string;
    tipoacao: ECrmAtcContaPagarTipoProcessamentoAcao;
    tipoprocessamento: ECrmAtcContaPagarTipoProcessamento;
}