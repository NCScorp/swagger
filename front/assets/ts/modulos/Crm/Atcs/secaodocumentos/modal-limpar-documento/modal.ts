import angular = require('angular');
import { AtcDocumentoTela } from '../classes/atcdocumentotela';
import { IAtcDocumentoTela } from '../classes/iatcdocumentotela';
import { CrmAtcDocumento } from '../../../Atcsdocumentos/classes/crmatcdocumento';
import { CrmAtcTipoDocumentoRequisitante } from '../../../Atcstiposdocumentosrequisitantes/classes/crmatctipodocumentorequisitante';
import { CrmAtcsdocumentos } from '../../../Atcsdocumentos/factory';

export class CrmModalAtcDocumentosLimparController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        '$scope',
        '$rootScope',
        'idocumentotela',
        'CrmAtcsdocumentos'
    ];

    private optionsDropzone: any;
    private modelDropzone = [];
    public documentoTela: AtcDocumentoTela = null;

    /**
     * Define se a tela está em processamento
     */
    public busy: boolean = false;
    /**
     * Define mensagem de processamento
     */
    public busyMensagem: string = 'Carregando...';

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public $scope: any,
        public $rootScope: any,
        public idocumentotela: IAtcDocumentoTela,
        public CrmAtcsdocumentos: CrmAtcsdocumentos
    ) {

        // Converto informações, pois a estrutura de dados do objeto fica modificada ao entrar na modal
        let dadosDocumentoTela: IAtcDocumentoTela = {
            atcsdocumentos: [],
            atctipodocumentorequisitante: CrmAtcTipoDocumentoRequisitante.getFromItemApi(idocumentotela.atctipodocumentorequisitante.getDados())
        }
        idocumentotela.atcsdocumentos.forEach((atcDoc) => {
            let atcDocObj = CrmAtcDocumento.getFromItemApi(atcDoc.getDados());

            // Preencho lista de documentos do Dropzone
            let makefile: any = {
                id: atcDoc.negociodocumento,
                name: atcDoc.tipodocumento.nome,
                type: atcDoc.tipomime,
                size: 0,
                created: atcDoc.created_at,
                url: atcDoc.url
            };
            
            this.modelDropzone.push(makefile);

            // Adiciono objeto de documento do atendimento a lista
            dadosDocumentoTela.atcsdocumentos.push(atcDocObj);
        });

        this.documentoTela = AtcDocumentoTela.getFromInterface(dadosDocumentoTela);

    }

    $onInit() {
        //Configurações Dropzone
        this.optionsDropzone = {

            url: 'http://google.com',
            paramName: "file",
            maxFilesize: 5,
            maxFiles: 0,
            addRemoveLinks: true,
            dictDefaultMessage: 'Solte o arquivo aqui ou clique para adicionar',
            dictRemoveFile: 'Remover',
            dictCancelUpload: 'Aguarde',
            dictMaxFilesExceeded: 'Você não pode enviar mais arquivos',
            dictCancelUploadConfirmation: 'Tem certeza que deseja cancelar o upload?',
            dictRemoveFileConfirmation: 'Tem certeza que deseja deletar o anexo?',
            autoProcessQueue: false,
            uploadMultiple: false,
            parallelUploads: 3,
            thumbnailMethod: 'crop',
            thumbnailHeight: 144,
            thumbnailWidth: 240
        };
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Callback chamado quando um arquivo é removido
     * @param file 
     */
     removeFile(file: any)  {
        // Ativo loading
        this.setBusy('Excluindo documento...');

        // Chamo requisição de exclusão
        let negociodocumento = file.uuid;
        this.CrmAtcsdocumentos.excluir(negociodocumento).then(() => {
            // Apresento mensagem de sucesso
            this.toaster.pop({
                type: 'success',
                title: 'O documento foi excluído com sucesso!'
            });
            // Removo atc documento da lista
            const documentoIndex = this.documentoTela.atcsdocumentos.findIndex((docFindIndex) => {
                return docFindIndex.negociodocumento == negociodocumento;
            });
            this.documentoTela.atcsdocumentos.splice(documentoIndex, 1);
        }).catch((response) => {
            if (response != null && response.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: "Ocorreu um erro ao excluir o documento!"
                });
            }
        }).finally(() => {
            this.busy = false;
            this.reloadScope();
        });
    }

    /**
     * Atualiza a visualização do escopo tela
     */
    reloadScope() {
        this.$scope.$applyAsync();
    }

    /**
     * Ativa o busy de processamento da tela.
     */
    private setBusy(mensagem = 'Carregando...'){
        this.busyMensagem = mensagem;
        this.busy = true;
        this.reloadScope();
    }

    isBusy(): boolean {
        return this.busy;
    }
}

export class CrmModalAtcDocumentosLimparService {
    static $inject = [
        '$uibModal'
    ];

    constructor(
        public $uibModal: any
    ) {}

    /* Incluindo o pai como parametro opcional*/
    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./modal.html'),
            controller: 'CrmModalAtcDocumentosLimparController',
            controllerAs: 'crm_mdl_atc_doc_lmp_cntrllr',
            windowClass: 'modal-lg-wrapper',
            resolve: {
                entity: async () => {
                    if (parameters!=undefined) {
                        let entity=parameters;
                        return entity;
                        
                    } else {
                        let entity='';
                        return entity;
                    }
                },
                constructors: () => {
                    return parameters;
                },
                idocumentotela: () => {
                    return subentity;
                }
            }
        });
    }

}