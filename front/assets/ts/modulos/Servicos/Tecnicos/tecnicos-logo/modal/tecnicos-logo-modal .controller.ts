import angular = require('angular');
import { NsjAuth } from '@nsj/core';

export class TecnicosLogoModalController {
    static $inject = [
        '$uibModalInstance',
        'entity',
        'nsjRouting',
        '$scope',
        'toaster'
    ];

    private apresentarBtnAddAnexo = false;
    private optionsDropzone: any = {};
    private addFile: (file: any) => void;
    private errorFile: (response: any) => void;
    private listFiles: any[] = [];
    private modelDropzone = [];
    private busy: boolean = false;

    private fileLogo: any = null;

    public rotaErpApi: boolean = true;

    constructor (
        public $uibModalInstance: any,
        public entity: any,
        public nsjRouting: any,
        public $scope: any,
        public toaster: any
    ){
        this.optionsDropzone = {
            url: this.nsjRouting.generate('servicos_tecnicos_logos_create', angular.extend({tecnico: this.entity.tecnico}), true, this.rotaErpApi),
            paramName: "logo",
            maxFiles: 1,
            addRemoveLinks: true,
            dictDefaultMessage: 'ARRASTE AQUI O ARQUIVO OU CLIQUE PARA ADICIONAR',
            dictRemoveFile: 'Remover',
            dictCancelUpload: 'Aguarde',
            dictMaxFilesExceeded: 'Você não pode enviar mais arquivos',
            dictCancelUploadConfirmation: 'Tem certeza que deseja cancelar o upload?',
            dictRemoveFileConfirmation: 'Tem certeza que deseja deletar o anexo?',
            autoProcessQueue: false,
            uploadMultiple: false,
            parallelUploads: 1,
            thumbnailMethod: 'crop',
            thumbnailHeight: 250,
            thumbnailWidth: 250,
            resizeWidth: 200,
            resizeHeight: 200,
            accept: (file, done) => {
                this.fileLogo = file;
                done();
            },
            removedFile(file){
                this.fileLogo = null;
            }
        };

            this.optionsDropzone.headers = NsjAuth.authService.addAuthHeader(this.optionsDropzone.headers);
        
    }

    isBusy(): boolean {
        return this.busy;
    }
    close() {
        if (!this.isBusy()) {
            this.$uibModalInstance.dismiss('fechar');
        }
    }

    salvar() {
        // Valido formato da imagem
        if (['image/png','image/jpeg'].indexOf(this.fileLogo.type) <= -1){
            this.toaster.pop({
                type: 'error',
                title: 'Formato da imagem deve ser png ou jpeg!'
            });

            return;
        }

        // Valido dimensões da imagem
        if (this.fileLogo.height > 200 || this.fileLogo.width > 200){
            this.toaster.pop({
                type: 'error',
                title: 'As dimensões máximas da imagem são 200 x 200 px!'
            });

            return;
        }

        if (this.fileLogo.height < 120 || this.fileLogo.width < 120){
            this.toaster.pop({
                type: 'error',
                title: 'As dimensões mínimas da imagem são 120 x 120 px!'
            });

            return;
        }

        // Valido tamanho máximo da imagem
        if ((this.fileLogo.size / 1024) > 500){
            this.toaster.pop({
                type: 'error',
                title: 'O tamanho máximo da imagem deve ser 500 KB!'
            });

            return;
        }

        this.busy = true;
        this.$scope.$broadcast('processQueue');
    }

    fecharTela(resposta: any){
        this.$uibModalInstance.close(resposta);
    }
    /**
     * Callback chamado quando o upload do arquivo é realizado com sucesso
     * @param response 
     */
    successFile (response: any) {
        this.busy = false;
        this.toaster.pop({
            type: 'success',
            title: 'Logo atualizada com sucesso!'
        });
        this.fecharTela({tipo: "sucesso", urlImagem: response.pathlogo});
    }

    ErrorFile(response: any) {
        this.busy = false;
        this.toaster.pop({
            type: 'error',
            title: 'Erro ao atualizar logo.'
        });
    }

    RemoveFile(response: any) {
        this.busy = false;
    }
}
