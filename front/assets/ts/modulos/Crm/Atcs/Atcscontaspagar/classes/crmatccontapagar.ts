import { Entity } from "../../../../Commons/classes/entity";
import { ICrmAtcContaPagar } from "./icrmatccontapagar";
import { IFornecedor } from "../../../../Ns/Fornecedores/classes/ifornecedor";
import { ECrmAtcContaPagarSituacao } from "./ecrmatccontapagarsituacao";
import { ECrmAtcContaPagarTipoProcessamento } from "./ecrmatccontapagartipoprocessamento";
import { ECrmAtcContaPagarTipoProcessamentoAcao } from "./ecrmatccontapagartipoprocessamentoacao";
import { ICrmItemProcessarContaPagar } from "./icrmitensprocessarcontaspagar";

export class CrmAtcContaPagar extends Entity<ICrmAtcContaPagar>{
    /**
     * Converte lista da api para lista de objetos de contas a pagar
     * @param arrListaApi 
     */
    static getFromListaApi(arrListaApi: ICrmAtcContaPagar[]): CrmAtcContaPagar[] {
        const arrListaObjeto: CrmAtcContaPagar[] = [];

        arrListaApi.forEach((itemApi) => {
            arrListaObjeto.push(new CrmAtcContaPagar(itemApi));
        });

        return arrListaObjeto;
    }

    get valorpagar(): number {
        return this.getPropertyToNumber('valorpagar');
    }

    set valorpagar(valorpagar: number) {
        this.setPropertyNumberToText('valorpagar', valorpagar);
    }

    get valoracordado(): number {
        return this.getPropertyToNumber('valoracordado');
    }

    get quantidade(): number {
        return this.dados.quantidade;
    }

    set quantidade(quantidade: number) {
        if (typeof(quantidade) == 'string'){
            quantidade = this.textToNumber(quantidade);
        }

        if (quantidade < 1) {
            quantidade = 1;
        }

        this.dados.quantidade = Math.round(quantidade);
    }

    get total(): number {
        return this.quantidade * this.valoracordado;
    }

    get descricao(): string {
        return this.dados.descricao;
    }

    get atccontaapagar(): string {
        return this.dados.atccontaapagar;
    }

    get negociodocumento(): string {
        return this.dados.negociodocumento;
    }

    get numerodocumento(): string {
        return this.dados.numerodocumento;
    }

    get orcamento(): string {
        return this.dados.orcamento;
    }

    set orcamento(orcamento: string) {
        this.dados.orcamento = orcamento;
    }

    get itemprocessarcontapagar(): ICrmItemProcessarContaPagar {
        return this.dados.itemprocessarcontapagar;
    }

    get datadocumento(): Date {
        if (this.dados.datadocumento != null) {
            return new Date(this.dados.datadocumento);
        }

        return null;
    }

    get prestador(): IFornecedor {
        return this.dados.prestador;
    }

    get situacao(): ECrmAtcContaPagarSituacao {
        // Documento ainda não foi enviado
        if (this.negociodocumento == null) {
            return ECrmAtcContaPagarSituacao.cacpsImpedido
        }
        // Documento enviado, porém não foi processado
        if (this.itemprocessarcontapagar == null || this.itemprocessarcontapagar.itemprocessarcontapagar == null) {
            return ECrmAtcContaPagarSituacao.cacpsNaoProcessado;
        }
        // Foi enviado para processamento
        if (this.itemprocessarcontapagar.tipoprocessamento == ECrmAtcContaPagarTipoProcessamento.cacptpEmProcessamento) {
            return ECrmAtcContaPagarSituacao.cacpsEmProcessamento;
        }
        // Ação de geração das contas a pagar
        if (this.itemprocessarcontapagar.tipoacao == ECrmAtcContaPagarTipoProcessamentoAcao.cacptpaGerar) {
            // Processamento realizado com sucesso
            if (this.itemprocessarcontapagar.tipoprocessamento == ECrmAtcContaPagarTipoProcessamento.cacptpProcessado) {
                return ECrmAtcContaPagarSituacao.cacpsProcessado;
            }
            // Processamento com falhas
            if (this.itemprocessarcontapagar.tipoprocessamento == ECrmAtcContaPagarTipoProcessamento.cacptpFalhaAoProcessar) {
                return ECrmAtcContaPagarSituacao.cacpsFalhaAoGerar;
            }
        }
        // Ação de cancelamento das contas a pagar
        if (this.itemprocessarcontapagar.tipoacao == ECrmAtcContaPagarTipoProcessamentoAcao.cacptpaCancelar) {
            // Processamento realizado com sucesso
            if (this.itemprocessarcontapagar.tipoprocessamento == ECrmAtcContaPagarTipoProcessamento.cacptpProcessado) {
                return ECrmAtcContaPagarSituacao.cacpsCancelado;
            }
            // Processamento com falhas
            if (this.itemprocessarcontapagar.tipoprocessamento == ECrmAtcContaPagarTipoProcessamento.cacptpFalhaAoProcessar) {
                return ECrmAtcContaPagarSituacao.cacpsFalhaAoCancelar;
            }
        }
    }
}