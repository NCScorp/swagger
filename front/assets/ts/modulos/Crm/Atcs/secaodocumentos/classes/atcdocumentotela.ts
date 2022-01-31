import { CrmAtcTipoDocumentoRequisitante } from "../../../Atcstiposdocumentosrequisitantes/classes/crmatctipodocumentorequisitante";
import { CrmAtcDocumento } from "../../../Atcsdocumentos/classes/crmatcdocumento";
import { ECrmAtcDocumentoSituacao } from "../../../Atcsdocumentos/classes/ecrmatcdocumentosituacao";
import { IAtcDocumentoTela } from "./iatcdocumentotela";

export class AtcDocumentoTela {
    /**
     * Tipo de Documento requisitado pelo atendimento
     */
    atctipodocumentorequisitante: CrmAtcTipoDocumentoRequisitante;
    /**
     * Documentos enviados no atendimento para o tipo de documento requisitado
     */
    atcsdocumentos: CrmAtcDocumento[] = [];

    get ultimodocumento(): CrmAtcDocumento {
        if (this.atcsdocumentos.length == 0) {
            return null;
        }

        let ultimoDocumento: CrmAtcDocumento = null;

        this.atcsdocumentos.forEach((docForeach) => {
            if (ultimoDocumento == null) {
                ultimoDocumento = docForeach;
            } else {
                if (docForeach.created_at.valueOf() > ultimoDocumento.created_at.valueOf()) {
                    ultimoDocumento = docForeach;
                }
            }
        });

        return ultimoDocumento;
    }

    get copiaexigida(): boolean {
        return this.atctipodocumentorequisitante.copiasimples || this.atctipodocumentorequisitante.copiaautenticada;
    }

    get possuirequisitanteexterno(): boolean {
        if (this.atctipodocumentorequisitante.requisitantecliente && this.atctipodocumentorequisitante.requisitantecliente.cliente != null) {
            return true;
        }

        if (this.atctipodocumentorequisitante.requisitantefornecedor && this.atctipodocumentorequisitante.requisitantefornecedor.fornecedor != null) {
            return true;
        }

        if (this.atctipodocumentorequisitante.requisitanteapolice && this.atctipodocumentorequisitante.requisitanteapolice.templateproposta != null) {
            return true;
        }

        return false
    }

    get requisitante(): string {
        let arrRequisitantes: string[] = [];

        if (this.atctipodocumentorequisitante.requisitantecliente && this.atctipodocumentorequisitante.requisitantecliente.cliente != null) {
            arrRequisitantes.push('Cliente');
        }

        if (this.atctipodocumentorequisitante.requisitantefornecedor && this.atctipodocumentorequisitante.requisitantefornecedor.fornecedor != null) {
            arrRequisitantes.push('Fornecedor');
        }

        if (this.atctipodocumentorequisitante.requisitanteapolice && this.atctipodocumentorequisitante.requisitanteapolice.templateproposta != null) {
            arrRequisitantes.push('Apólice');
        }

        if (this.atctipodocumentorequisitante.requisitantenegocio) {
            arrRequisitantes.push('Atendimento Comercial');
        }

        if (arrRequisitantes.length == 0) {
            return '-';
        }
        
        let retorno = '';

        arrRequisitantes.forEach((requisitanteForeach) => {
            if (retorno == '') {
                retorno = requisitanteForeach;
            } else {
                retorno += ' / ' + requisitanteForeach;
            }
        });

        return retorno;
    }

    get analiseFinalizada(): boolean {
        return [
            ECrmAtcDocumentoSituacao.cadsPreAprovado,
            ECrmAtcDocumentoSituacao.cadsRecusado
        ].indexOf(this.ultimodocumento.status) > -1;
    }

    get status(): number {
        const ultimoDocumento = this.ultimodocumento;

        //Se não houver último documento, ainda não foi enviado. Usando -1 para representar isso
        if (!ultimoDocumento) {
            return -1;
        }

        //Do contrário, retorno o status do tipo de documento
        else{
            return ultimoDocumento.status;
        }

    }

    get statusDescricao(): string {
        const ultimoDocumento = this.ultimodocumento;

        if (!ultimoDocumento) {
            return 'NÃO ENVIADO';
        }

        switch (ultimoDocumento.status) {
            case ECrmAtcDocumentoSituacao.cadsPendente: {
                return 'PENDENTE';
            }
            case ECrmAtcDocumentoSituacao.cadsRecebido: {
                return 'RECEBIDO';
            }
            case ECrmAtcDocumentoSituacao.cadsPreAprovado: {
                return 'PRÉ-APROVADO';
            }
            case ECrmAtcDocumentoSituacao.cadsEnviadoRequisitante: {
                return 'ENVIADO PARA REQUISITANTE';
            }
            case ECrmAtcDocumentoSituacao.cadsAprovado: {
                return 'APROVADO';
            }
            case ECrmAtcDocumentoSituacao.cadsRecusado: {
                return 'RECUSADO';
            }
        
            default: return '';
        }
    }

    get statusClasseLabel(): string {
        const ultimoDocumento = this.ultimodocumento;

        if (!ultimoDocumento) {
            return 'label-default';
        }

        switch (ultimoDocumento.status) {
            case ECrmAtcDocumentoSituacao.cadsPendente: {
                return 'label-default';
            }
            case ECrmAtcDocumentoSituacao.cadsRecebido: {
                return 'label-warning';
            }
            case ECrmAtcDocumentoSituacao.cadsPreAprovado: {
                return 'label-success';
            }
            case ECrmAtcDocumentoSituacao.cadsEnviadoRequisitante: {
                return 'label-primary';
            }
            case ECrmAtcDocumentoSituacao.cadsAprovado: {
                return 'label-success';
            }
            case ECrmAtcDocumentoSituacao.cadsRecusado: {
                return 'label-danger';
            }

            default: return 'label-default';
        }
    }

    /**
     * Cria objeto AtcDocumentoTela a partir da interface de dados IAtcDocumentoTela
     * @param documentoTelaInterface 
     */
    static getFromInterface(documentoTelaInterface: IAtcDocumentoTela): AtcDocumentoTela{
        const atcDocumento = new AtcDocumentoTela();
        atcDocumento.atcsdocumentos = documentoTelaInterface.atcsdocumentos;
        atcDocumento.atctipodocumentorequisitante = documentoTelaInterface.atctipodocumentorequisitante;

        return atcDocumento;
    }

    toInterface(): IAtcDocumentoTela {
        const iAtcDocTela: IAtcDocumentoTela = {
            atctipodocumentorequisitante: this.atctipodocumentorequisitante,
            atcsdocumentos: this.atcsdocumentos
        }

        return iAtcDocTela;
    }
}