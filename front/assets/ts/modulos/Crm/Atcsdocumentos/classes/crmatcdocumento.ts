import { Entity } from '../../../Commons/classes/entity';
import { ICrmAtcDocumento } from "./icrmatcdocumento";
import { INsTipoDocumento } from '../../../Ns/Tiposdocumentos/classes/instipodocumento';
import { IUser } from '../../../Commons/classes/iuser';
import { ECrmAtcDocumentoSituacao } from './ecrmatcdocumentosituacao';
import { ECrmAtcDocumentoTipoNotaFiscal } from './ecrmatcdocumentostiponf';
import { IOrcamento } from '../../Orcamentos/classes/iorcamento';
import { IAtc } from '../../Atcs/classes/iatc';

export class CrmAtcDocumento extends Entity<ICrmAtcDocumento>{
    constructor(
        protected dados: ICrmAtcDocumento = {}
    ){
        super(dados);
        this.dados.seriedocumento = this.dados.seriedocumento ? this.dados.seriedocumento : '';
        this.dados.numerodocumento = this.dados.numerodocumento ? this.dados.numerodocumento : '';
    }

    /**
     * Converte lista da api para lista de objetos de CrmAtcTipoDocumentoRequisitante
     * @param arrListaApi 
     */
    static getFromListaApi(arrListaApi: ICrmAtcDocumento[]): CrmAtcDocumento[] {
        const arrListaObjeto: CrmAtcDocumento[] = [];

        arrListaApi.forEach((itemApi) => {
            arrListaObjeto.push( this.getFromItemApi(itemApi) );
        });

        return arrListaObjeto;
    }

    /**
     * Converte item da api para objeto de CrmAtcDocumento
     * @param arrListaApi 
     */
    static getFromItemApi(itemApi: ICrmAtcDocumento): CrmAtcDocumento {
        const atcDocumento = new CrmAtcDocumento(itemApi);

        return atcDocumento;
    }

    get negociodocumento(): string {
        return this.dados.negociodocumento;
    }

    get negocio(): IAtc {
        return this.dados.negocio;
    }

    set negocio(negocio: IAtc) {
        this.dados.negocio = negocio;
    }

    get tipomime(): string {
        return this.dados.tipomime;
    }

    set tipomime(tipomime: string) {
        this.dados.tipomime = tipomime;
    }

    get tipodocumento(): INsTipoDocumento {
        return this.dados.tipodocumento;
    }

    set tipodocumento(tipodocumento: INsTipoDocumento) {
        this.dados.tipodocumento = tipodocumento;
    }

    get url(): string {
        return this.dados.url;
    }

    set url(url: string) {
        this.dados.url = url;
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

    get seriedocumento(): string {
        return this.dados.seriedocumento;
    }

    set seriedocumento(seriedocumento: string) {
        this.dados.seriedocumento = seriedocumento;
    }

    get numerodocumento(): string {
        return this.dados.numerodocumento;
    }

    set numerodocumento(numerodocumento: string) {
        this.dados.numerodocumento = numerodocumento;
    }

    get emissaodocumento(): string {
        return this.dados.emissaodocumento;
    }

    set emissaodocumento(emissaodocumento: string) {
        this.dados.emissaodocumento = emissaodocumento;
    }

    get tiponf(): ECrmAtcDocumentoTipoNotaFiscal {
        return this.dados.tiponf;
    }

    set tiponf(tiponf: ECrmAtcDocumentoTipoNotaFiscal) {
        this.dados.tiponf = tiponf;
    }

    get orcamentos(): IOrcamento[] {
        return this.dados.orcamentos;
    }

    set orcamentos(orcamentos: IOrcamento[]) {
        this.dados.orcamentos = orcamentos;
    }

    get descricao(): string {
        return this.dados.descricao;
    }

    set descricao(descricao: string) {
        this.dados.descricao = descricao;
    }

    get status(): ECrmAtcDocumentoSituacao {
        return this.dados.status;
    }

    set status(status: ECrmAtcDocumentoSituacao) {
        this.dados.status = status;
    }
}