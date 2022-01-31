// import { Tree } from '../../Commons/tree';
// import { CrmOrcamentosObservacoesSubModalService } from './orcamento-observacoes-submodal';
import { CrmAtcsfollowupsanexos } from '././../../Crm/Atcsfollowupsanexos/factory';

export class NsModalAnexoController {
    static $inject = [
        'toaster',
        '$uibModalInstance',
        'entity',
        '$scope',
        'nsjRouting',
        '$http',
        '$rootScope',
        'moment',
        'CrmAtcsfollowupsanexos'
    ];

    private addFile: (file: any) => void;
    //private removeFile: (file: any) => void;
    // private successFile: (response: any) => void;
    private errorFile: (response: any) => void;
    private listFiles: any[] = [];
    private optionsDropzone: any;
    private modelDropzone = [];

    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public $scope: any,
        public nsjRouting: any,
        public $http: any,
        public $rootScope: any,
        protected moment: any,
        public CrmAtcsfollowupsanexos: CrmAtcsfollowupsanexos,
    ) {
    }
    $onInit() {
        // Popular componente de anexos com os dados gravados
        if (!this.entity.followup.negociofollowupanexo) {
            this.entity.followup.negociofollowupanexo = [];
        }
        if (this.entity.followup.negociofollowupanexo.length>0) {

            this.entity.followup.negociofollowupanexo.forEach(item => {
            
                let makefile = {
                    id: item.negociofollowupanexo,
                    name: item.nomearquivo,
                    type: item.tipo,
                    size: item.tamanho,
                    created: item.data,
                    url: item.url
                };
                
                this.modelDropzone.push(makefile);
            
            });
            
        }
        //###################


        //Configurações Dropzone

        this.optionsDropzone = {

            url: this.nsjRouting.generate('_uploader_upload_documentos', angular.extend({}), true),
            // url: 'http://google.com',
            paramName: "file",
            maxFilesize: 5,
            maxFiles: 100,
            addRemoveLinks: true,
            dictDefaultMessage: 'Solte o arquivo aqui ou clique para adicionar',
            dictRemoveFile: 'Remover',
            dictCancelUpload: 'Aguarde',
            dictMaxFilesExceeded: 'Você não pode enviar mais arquivos',
            dictCancelUploadConfirmation: 'Tem certeza que deseja cancelar o upload?',
            dictRemoveFileConfirmation: 'Tem certeza que deseja deletar o anexo?',
            autoProcessQueue: true,
            uploadMultiple: true,
            parallelUploads: 3,
            thumbnailMethod: 'crop',
            thumbnailHeight: 144,
            thumbnailWidth: 240
        
        };
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }    

    private addList(file: any) {

        this.listFiles.push(file);
    }

    /**
     * Callback chamado quando um arquivo é adicionado ao componente, antes do upload ser realizado
     * @param file 
     */
    AddFile(file: any) {
        //Se arquivo já estiver criado no backend, adiciono botão de download aqui
        if (file.uuid != undefined && file.uuid != null){
            // this.adicionarAcaoDownload(file.previewTemplate, file.url);
            
            const img = file.previewTemplate.querySelector('.dz-image').querySelector('.container-imagem').querySelector('img');

            //Adiciono o evento caso tenha dado erro para verificar se é um arquivo ou imagem com erro de carregamento
            img.addEventListener("error", () => {
                //Se foi uma imagem que não carregou direito
                if (file.type.indexOf('image') > -1){
                    img.setAttribute('class', img.className + ' erro-carregamento');
                }
                else{
                    img.setAttribute('class', img.className + ' file');
                }
                
            });
        }

        //Me certifico de sempre ser um image preview em vez de file preview
        file.previewTemplate.setAttribute('class', file.previewTemplate.className.replace('dz-file-preview', 'dz-image-preview'));
        
        this.addFile({ file: file });
    }
    /**
     * Callback chamado quando um arquivo é removido
     * @param file 
     */
     removeFile(file: any)  {

        let fileId = file.upload ? file.upload.uuid : file.uuid;

        let obj = this.entity.followup.negociofollowupanexo.filter((item) => {
            return item.negociofollowupanexo == fileId
        }); // item.id = id do anexo

        if (obj.length) {

            if (this.entity.followup) {
                this.CrmAtcsfollowupsanexos.constructors = { negociofollowup: this.entity.followup.id  };
            }

            let atcfollowupanexoId = obj[0].negociofollowupanexo;

            /* Passo true no segundo argumento(arg force) para não pedir confirmação de exclusão, 
            * pois o próprio componente já possui suporte para confirmação */
            this.CrmAtcsfollowupsanexos.delete(atcfollowupanexoId, true); 
        }

        this.$scope.$on("crm_atcsfollowupsanexos_deleted",  (event) => {
            this.entity.followup.negociofollowupanexo.pop(obj[0]);
        });

    }

    /**
     * Callback chamado quando o upload do arquivo é realizado com sucesso
     * @param response 
     */
    successFile (response: any) {

        // if (!this.entity.ordemservico && !this.entity.ordemservicotemp) {
        // this.entity.ordemservicotemp = this.uuidv4();
        // }
        
        let atcfollowupId = this.entity.followup.id;
        
        let entity = {
        
            uploadid: response.file.upload.uuid,
            nomearquivo: response.file.name,
            negociofollowup: atcfollowupId,
            url: response.url,
            tipo: response.mimetype,
            tamanho: response.file.size                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
        }

        //Busco elemento de link de download do anexo e deixo invisível até buscar a url correta.
        const divAcaoDownload: Element = response.file.previewTemplate
            .querySelector('.acoes-anexo').querySelector('.acao-download');
        
        divAcaoDownload.setAttribute('class', divAcaoDownload.getAttribute('class') + ' hide');
        
        
        this.CrmAtcsfollowupsanexos.constructors = { negociofollowup: atcfollowupId }; // importar factory de atcs followup
        this.CrmAtcsfollowupsanexos.save(entity, true);
        
        this.$scope.$on('crm_atcsfollowupsanexos_submitted', (event: any, args: any) => {
            entity.url = args.response.data.url;
            this.entity.followup.negociofollowupanexo.push(args.entity);
                                                                                                                                                                                                                        
            let makefile = {
                
                id: args.entity.uploadid,
                name: args.entity.nomearquivo,
                type: args.entity.tipo,
                size: args.entity.tamanho,
                created: args.entity.data,
                url: entity.url
            };
            
            this.modelDropzone.push(makefile);

            // Atualizo a URL do link de download
            const linkDownload: Element = divAcaoDownload.querySelector('a');
            linkDownload.setAttribute('href', entity.url);
            // Mostro botão de download
            divAcaoDownload.setAttribute('class', divAcaoDownload.getAttribute('class').replace('hide', ''));
        });
        
    }

    /**
     * Callback chamado quando acontece um erro no upload do arquivo
     * @param response
     */
    ErrorFile(response: any) {
        this.errorFile({ response: response });
    }
}

export class NsModalAnexoService {
    static $inject = ['CrmPropostascapitulos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }

    /* Incluindo o pai como parametro opcional*/
    open(parameters: any, subentity: any, paiId: any) {

            return this.$uibModal.open({
                template: require('../../Crm/Atcsfollowups/modal-anexo.html'),
                controller: 'CrmModalAnexoController',
                controllerAs: 'crm_mdl_anx_cntrllr',
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
                    }
                }
            });
    }

}
