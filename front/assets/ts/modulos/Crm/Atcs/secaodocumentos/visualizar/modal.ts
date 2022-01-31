import angular = require('angular');

import { AtcDocumentoTela } from './../classes/atcdocumentotela';
import { ECrmAtcDocumentoSituacao } from '../../../Atcsdocumentos/classes/ecrmatcdocumentosituacao';
import { CrmAtcDocumento } from '../../../Atcsdocumentos/classes/crmatcdocumento';
import { IAtcDocumentoTela } from '../classes/iatcdocumentotela';
import { CrmAtcTipoDocumentoRequisitante } from '../../../Atcstiposdocumentosrequisitantes/classes/crmatctipodocumentorequisitante';

export class CrmAtcsDocumentosVisualizarModalController {
    static $inject = [
        'toaster', 
        '$uibModalInstance', 
        'constructors', 
        'idocumentotela', 
        '$http', 
        'nsjRouting', 
        'moment'
    ];
    public action: string;
    public form: any;
    public submitted: boolean = false;
    /**
     * Define se a análise do documento estava finalizada ao abrir a modal de visualização
     */
    public analiseFinalizada: boolean = false;
    /**
     * Situação utilizada para realizar a análise do documento
     */
    public novoStatus: ECrmAtcDocumentoSituacao;
    /**
     * Descrição utilizada para realizar a análise do documento
     */
    public descricao: string;

    public ECrmAtcDocumentoSituacao = ECrmAtcDocumentoSituacao;
    public documentoTela: AtcDocumentoTela = null;
    /**
     * Lista de imagens apresentadas no carrosel
     */
    public arrImagensCarousel: any[] = [];

    constructor(
        public toaster: any,
        public $uibModalInstance: any, 
        public constructors: any,
        public idocumentotela: IAtcDocumentoTela,
        public $http: any,
        public nsjRouting: any,
        public moment: any,
    ) {
        // Converto informações, pois a estrutura de dados do objeto fica modificada ao entrar na modal
        let dadosDocumentoTela: IAtcDocumentoTela = {
            atcsdocumentos: [],
            atctipodocumentorequisitante: CrmAtcTipoDocumentoRequisitante.getFromItemApi(idocumentotela.atctipodocumentorequisitante.getDados())
        }
        idocumentotela.atcsdocumentos.forEach((atcDoc) => {
            let atcDocObj = CrmAtcDocumento.getFromItemApi(atcDoc.getDados());

            this.arrImagensCarousel.push({
                link: atcDocObj.url,
                src: atcDocObj.url,
            });

            dadosDocumentoTela.atcsdocumentos.push(atcDocObj);
        });

        idocumentotela.atcsdocumentos = idocumentotela.atcsdocumentos.sort((atcDocA, atcDocB) => {
            // Organiza do mais antigo para o mais novo
            if (atcDocA.created_at.valueOf() < atcDocB.created_at.valueOf()) {
                return -1;
            } else {
                return 1;
            }
        })

        this.documentoTela = AtcDocumentoTela.getFromInterface(dadosDocumentoTela);

        // Preencho valores iniciais dos dados que podem ser alterados na tela
        this.analiseFinalizada = this.documentoTela.analiseFinalizada;
        this.novoStatus = this.documentoTela.ultimodocumento.status;
        this.descricao = this.documentoTela.ultimodocumento.descricao;
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Realiza a finalização da análise do documento.
     */
    finalizarAnalise(){
        // Preenchos dados de análise
        const ultimoDocumento = new CrmAtcDocumento(this.documentoTela.ultimodocumento.getDados());
        ultimoDocumento.status = this.novoStatus;
        ultimoDocumento.descricao = this.descricao;

        // Retorno atc documento com informações preenchidas
        this.$uibModalInstance.close(ultimoDocumento);
    }
}

/**
 * Service responsável por apresentar modal
 */
export class CrmAtcsDocumentosVisualizarModalService {
    static $inject = [
        '$uibModal'
    ];
 
    constructor(
        public $uibModal: any
    ) {}
 
    open(parameters: any = {}, documentoTela: IAtcDocumentoTela) {
        return this.$uibModal.open({
            template: require('./modal.html'),
            controller: 'CrmAtcsDocumentosVisualizarModalController',
            controllerAs: 'ctrlModal',
            windowClass: '',
            size: 'lg',
            resolve: {
                constructors: () => {
                    return parameters;
                },
                idocumentotela: () => {
                    return documentoTela;
                }
            }
        });
    }
}
