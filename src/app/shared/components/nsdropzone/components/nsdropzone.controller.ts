import {DropzoneFile, DropzoneOptions, DropzoneResponseFile, DropzoneSuccessFile} from "../interfaces/dropzone";

export class NSDropzoneController {

    static $inject = ['$scope', 'moment', '$element'];

    private apresentarBtnAddAnexo: boolean = true;
    private addFile: (file: { file: DropzoneFile }) => void;
    private removeFile: (file: { file: DropzoneFile }) => void;
    private successFile: (response: { response: DropzoneResponseFile }) => void;
    private errorFile: (response: any) => void;
    private listFiles: DropzoneSuccessFile[] = [];
    private showTable: boolean = false;
    private options: DropzoneOptions;
    private model: any[] = [];
    private elemento: HTMLElement;
    private clickAddEvent: (event:JQuery.Event) => void = null;

    constructor(protected $scope: any, protected moment: any, protected $element: any) {
        (<any>window).Dropzone.autoDiscover = false;
    }

    $onInit() {
        //Elemento que representa o html do componente.
        this.elemento = this.$element[0];
        this.apresentarBtnAddAnexo = (this.apresentarBtnAddAnexo != undefined) ? this.apresentarBtnAddAnexo : true;

        if (this.options != undefined) {
            //Caso não tenha sido definido um previewTemplate nos options, adiciono o previewTemplate do componente
            if (this.options.previewTemplate == undefined) {
                this.options.previewTemplate = this.elemento.querySelector('.preview-template-dropzone').innerHTML;
            }
        } else {
            this.options = {previewTemplate: this.elemento.querySelector('.preview-template-dropzone').innerHTML};
        }

        //Necessário setar essa opção para ter um botão externo de adicionar anexo
        this.options.clickable = true;

        if (this.apresentarBtnAddAnexo) {
            this.clickAddEvent = (event) => {
                //Previno o comportamento padrão do botão
                event.preventDefault();
                //Chamo o click do próprio dropzone, que fica no hiddenFileInput
                const dropzone = this.elemento.querySelector('.dropzone');
                (dropzone as any).dropzone.hiddenFileInput.click();
            };
        }

        if (this.model) {
            this.model.forEach(item => {

                let file = {
                    id: item.id,
                    name: item.name,
                    type: item.type,
                    size: item.size,
                    created: this.moment(item.created).format('D/MM/YYYY H:mm:ss'),
                    url: item.url
                };

                this.addList(file);

            });
        }
    }

    addAnexo(event: JQuery.Event) {
        this.clickAddEvent(event);
    }

    private addList(file: DropzoneSuccessFile) {
        this.listFiles.push(file);
    }

    /**
     * Callback chamado quando um arquivo é adicionado ao componente, antes do upload ser realizado
     * @param file
     */
    AddFile(file: DropzoneFile) {
        //Se arquivo já estiver criado no backend, adiciono botão de download aqui
        if (file.uuid != undefined && file.uuid != null) {
            this.adicionarAcaoDownload(file.previewTemplate, file.url);

            const img = file.previewTemplate.querySelector('.dz-image').querySelector('.container-imagem').querySelector('img');

            //Adiciono o evento caso tenha dado erro para verificar se é um arquivo ou imagem com erro de carregamento
            img.addEventListener("error", () => {
                //Se foi uma imagem que não carregou direito
                if (file.type.indexOf('image') > -1) {
                    img.setAttribute('class', img.className + ' erro-carregamento');
                } else {
                    img.setAttribute('class', img.className + ' file');
                }

            });
        }

        //Me certifico de sempre ser um image preview em vez de file preview
        file.previewTemplate.setAttribute('class', file.previewTemplate.className.replace('dz-file-preview', 'dz-image-preview'));

        this.addFile({file: file});
    }

    /**
     * Adiciona ao previewTemplate ação de download com o link da url passada.
     * @param previewTemplate
     * @param url
     */
    private adicionarAcaoDownload(previewTemplate: HTMLElement, url: string) {
        //Crio html da ação
        let innerHtmlDownload = `
            <a href="${url}" target="_blank">
                <i class="fas fa-download"></i>
            </a>
            <div class="acao-label-container">
                <div class="acao-label">Fazer o download</div>
            </div>
        `;
        let el = document.createElement('div');
        el.setAttribute('class', "acao-anexo acao-download");
        el.innerHTML = innerHtmlDownload;

        //Adiciono ação ao template
        let acoes = previewTemplate.querySelector('.acoes-anexo');
        acoes.appendChild(el);
    }

    /**
     * Callback chamado quando um arquivo é removido
     * @param file
     */
    RemoveFile(file: DropzoneFile) {
        let fileId = file.upload ? file.upload.uuid : file.uuid;

        let obj = this.listFiles.filter(item => item.id == fileId);

        if (obj.length) {

            let idx = this.listFiles.indexOf(obj[0]);
            this.listFiles.splice(idx, 1);
            this.$scope.$digest();
        }

        this.removeFile({file: file});
    }

    /**
     * Callback chamado quando o upload do arquivo é realizado com sucesso
     * @param response
     */
    SuccessFile(response: DropzoneResponseFile) {
        let file = {
            id: response.file.upload.uuid,
            name: response.file.name,
            type: response.mimetype,
            size: response.file.size,
            created: this.moment(response.file.lastModifiedDate).format('D/MM/YYYY H:mm:ss'),
            url: response.url
        };

        this.addList(file);

        this.$scope.$digest();

        //Adiciono ação de download
        this.adicionarAcaoDownload(response.file.previewTemplate, response.url);

        this.successFile({response: response});
    }

    /**
     * Callback chamado quando acontece um erro no upload do arquivo
     * @param response
     */
    ErrorFile(response: any) {
        this.errorFile({response: response});
    }


}
