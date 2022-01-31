import  Dropzone =  require('dropzone');

export class NSDropzoneController {

    static $inject = ['$scope', 'moment', '$element'];

    private addFile: (file: any) => void;
    private removeFile: (file: any) => void;
    private successFile: (response: any) => void;
    private errorFile: (response: any) => void;
    private listFiles: any[] = [];
    private showTable: boolean = false;
    private options: any;
    private model: any[] = [];
    private elemento: any;
    private envioDesabilitado: boolean = false;

    constructor(protected $scope: any, protected moment: any, protected $element: any) {
        Dropzone.autoDiscover = false;
    }

    $onInit() {
        //Elemento que representa o html do componente.
        this.elemento = this.$element[0];
        
        if (this.options != undefined){
            //Caso não tenha sido definido um previewTemplate nos options, adiciono o previewTemplate do componente
            if (this.options.previewTemplate == undefined){
                this.options.previewTemplate = this.elemento.querySelector('.preview-template-dropzone').innerHTML;
            }
        }
        else{
            this.options = {previewTemplate: this.elemento.querySelector('.preview-template-dropzone').innerHTML};
        }

        this.showTable = true;
        if (this.showTable) {

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
    }

    addList(file: any) {
        this.listFiles.push(file);
    }

    /**
     * Callback chamado quando um arquivo é adicionado ao componente, antes do upload ser realizado
     * @param file 
     */
    AddFile(file: any) {
        //Se arquivo já estiver criado no backend, adiciono botão de download aqui
        if (file.uuid != undefined && file.uuid != null){//Adiciono ação de download
            let url = '';
            this.listFiles.forEach(savedFile => {
                if(url===''){
                    if(savedFile.id === file.uuid){
                        url = savedFile.url;
                    }
                }
            });
            this.adicionarAcaoDownload(file.previewTemplate, url);
            
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

    RemoveFile(file: any) {

        let fileId = file.upload ? file.upload.uuid : file.uuid;

        let obj = this.listFiles.filter(item => item.id == fileId);

        if (obj.length) {

            let idx = this.listFiles.indexOf(obj[0]);
            this.listFiles.splice(idx, 1);
            this.$scope.$digest();
        }

        this.removeFile({ file: file });
    }

    SuccessFile(response: any) {

        if (this.showTable) {

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
        }

        //Adiciono ação de download
        let url = '';
        this.listFiles.forEach(savedFile => {
            if(url===''){
                if(savedFile.id === response.file.upload.uuid){
                    url = savedFile.url;
                }
            }
        });
        this.adicionarAcaoDownload(response.file.previewTemplate, url);

        this.successFile({ response: response });
    }

    ErrorFile(response: any) {
        this.errorFile({ response: response });
    }

    /**
     * Adiciona ao previewTemplate ação de download com o link da url passada.
     * @param previewTemplate 
     * @param url 
     */
    private adicionarAcaoDownload(previewTemplate: any, url: string){

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

}
