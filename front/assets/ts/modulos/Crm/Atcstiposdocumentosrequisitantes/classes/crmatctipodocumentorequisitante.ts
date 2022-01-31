import { Entity } from '../../../Commons/classes/entity';
import { ICrmAtcTipoDocumentoRequisitante } from "./icrmatctipodocumentorequisitante";
import { INsTipoDocumento } from '../../../Ns/Tiposdocumentos/classes/instipodocumento';
import { IUser } from '../../../Commons/classes/iuser';
import { IFornecedor } from '../../../Ns/Fornecedores/classes/ifornecedor';
import { INsCliente } from '../../../Ns/Clientes/classes/inscliente';
import { ICrmTemplateProposta } from '../../Templatespropostas/classes/icrmtemplateproposta';
// import { IFornecedor } from "../../../../Ns/Fornecedores/classes/ifornecedor";
// import { ECrmAtcContaPagarSituacao } from "./ecrmatccontapagarsituacao";
// import { ECrmAtcContaPagarTipoProcessamento } from "./ecrmatccontapagartipoprocessamento";
// import { ECrmAtcContaPagarTipoProcessamentoAcao } from "./ecrmatccontapagartipoprocessamentoacao";
// import { ICrmItemProcessarContaPagar } from "./icrmitensprocessarcontaspagar";

export class CrmAtcTipoDocumentoRequisitante extends Entity<ICrmAtcTipoDocumentoRequisitante>{
    /**
     * Converte lista da api para lista de objetos de CrmAtcTipoDocumentoRequisitante
     * @param arrListaApi 
     */
    static getFromListaApi(arrListaApi: ICrmAtcTipoDocumentoRequisitante[]): CrmAtcTipoDocumentoRequisitante[] {
        const arrListaObjeto: CrmAtcTipoDocumentoRequisitante[] = [];

        arrListaApi.forEach((itemApi) => {
            arrListaObjeto.push( this.getFromItemApi(itemApi) );
        });

        return arrListaObjeto;
    }

    /**
     * Converte item da api para objeto de CrmAtcTipoDocumentoRequisitante
     * @param arrListaApi 
     */
    static getFromItemApi(itemApi: ICrmAtcTipoDocumentoRequisitante): CrmAtcTipoDocumentoRequisitante {
        const tipoDocumentoRequisitante = new CrmAtcTipoDocumentoRequisitante(itemApi);

        return tipoDocumentoRequisitante;
    }

    get negociotipodocumentorequisitante(): string {
        return this.dados.negociotipodocumentorequisitante;
    }

    get tipodocumento(): INsTipoDocumento {
        return this.dados.tipodocumento;
    }

    set tipodocumento(tipodocumento: INsTipoDocumento) {
        this.dados.tipodocumento = tipodocumento;
    }

    get copiasimples(): boolean {
        return this.dados.copiasimples;
    }

    get copiaautenticada(): boolean {
        return this.dados.copiaautenticada;
    }

    get original(): boolean {
        return this.dados.original;
    }

    get permiteenvioemail(): boolean {
        return this.dados.permiteenvioemail;
    }

    get requisitantenegocio(): boolean {
        return this.dados.requisitantenegocio;
    }

    get pedirinformacoesadicionais(): boolean {
        return this.dados.pedirinformacoesadicionais;
    }

    get naoexibiremrelatorios(): boolean {
        return this.dados.naoexibiremrelatorios;
    }

    get created_by(): IUser {
        if(typeof (this.dados.created_by) == 'string') {
            this.dados.created_by = JSON.parse(this.dados.created_by);
        }

        return this.dados.created_by;
    }

    set created_at(created_at: Date) {
        this.dados.created_at = created_at.toISOString();
    }

    get created_at(): Date {
        return new Date(this.dados.created_at);
    }

    get requisitantecliente(): INsCliente {
        return this.dados.requisitantecliente;
    }

    get requisitantefornecedor(): IFornecedor {
        return this.dados.requisitantefornecedor;
    }

    get requisitanteapolice(): ICrmTemplateProposta {
        return this.dados.requisitanteapolice;
    }
}