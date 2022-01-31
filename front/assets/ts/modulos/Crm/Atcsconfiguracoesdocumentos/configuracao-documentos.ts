import angular = require('angular');
import { IAtcConfiguracaoDocumento, EnumTipoGeracaoPrestadora, EnumTipoGeracaoSeguradora, EnumTipoDocumentoItem, IAtcConfiguracaoDocumentoItem, IAtcConfiguracaoTemplateEmail } from './classes';
import { CrmAtcsconfiguracoesdocumentos } from './factory';

export class CrmConfiguracaoDocumentosController {

    static $inject = [
        '$scope', 
        'toaster', 
        'utilService', 
        '$http',
        'nsjRouting',
        'CrmAtcsconfiguracoesdocumentos',
    ];

    // Coloco enums no controller para utilizar na view
    private EnumTipoGeracaoPrestadora = EnumTipoGeracaoPrestadora;
    private EnumTipoGeracaoSeguradora = EnumTipoGeracaoSeguradora;
    public form;

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public documentosFop: any;
    public configuracaoDocs: any;
    public atcConfiguracaoDocumento: IAtcConfiguracaoDocumento;
    public carregouSemErro: boolean = true;
    public documentosItensPrestadora: IAtcConfiguracaoDocumentoItemTela[] = [];
    public documentosItensSeguradora: IAtcConfiguracaoDocumentoItemTela[] = [];
    public templatesEmailsTela: IAtcConfiguracaoTemplateEmailTela[] = [];
    public templatesEmails: any;
    public enviandoRequisicao: boolean = false;

    constructor(
        public $scope: any, 
        public toaster: any, 
        public utilService: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        private CrmAtcsconfiguracoesdocumentos: CrmAtcsconfiguracoesdocumentos
        ) {
    }

    async $onInit() {

        try {
            this.busy = true;

            // Evento: Salvou configuração com sucesso
            this.$scope.$on('crm_atcsconfiguracoesdocumentos_submitted', () => {
                this.toaster.pop({
                    type: 'success',
                    title: 'A Configuração de Documento foi salva com sucesso!'
                });
                
                this.enviandoRequisicao = false;
            });

            // Evento: Ocorreu um erro ao salvar configuração
            this.$scope.$on('crm_atcsconfiguracoesdocumentos_submit_error', (event: any, args: any) => {
                this.toaster.pop({
                    type: 'error',
                    title: args.response.data.message
                });

                this.enviandoRequisicao = false;
            });

            this.atcConfiguracaoDocumento = await this.getConfiguracaoFromApi();
            this.documentosFop = await this.carregarDocumentosFop();
            this.templatesEmails = await this.carregarTemplatesEmail();
            
            if(!this.atcConfiguracaoDocumento){
                this.action = 'POST';
                this.atcConfiguracaoDocumento = {
                    tipogeracaoprestadora: 1,
                    tipogeracaoseguradora: 1,
                    atcsconfiguracoesdocumentositens: [],
                    atcsconfiguracoestemplatesemails: []
                };
            }

            if(this.atcConfiguracaoDocumento.atcconfiguracaodocumento){
                this.action = 'PUT';
            }

            //Percorrendo os documentos itens para criar as duas listas
            if(this.atcConfiguracaoDocumento.atcsconfiguracoesdocumentositens.length > 0){

                for (let i = 0; i < this.atcConfiguracaoDocumento.atcsconfiguracoesdocumentositens.length; i++) {
                    const documentoItem = this.atcConfiguracaoDocumento.atcsconfiguracoesdocumentositens[i];

                    // Prestadora
                    if (documentoItem.tipo == EnumTipoDocumentoItem.tdiPrestadora){
                        this.documentosItensPrestadora.push(
                            {documentoitem: documentoItem, checked: true}
                        );
                    }
                    
                    // Seguradora
                    else{
                        this.documentosItensSeguradora.push(
                            {documentoitem: documentoItem, checked: true}
                        );
                    }
                    
                }
                this.reloadScope();
            }

            // Uso a lista de documentosfop para saber se há algum documentofop que não pertence aos docsitensprest ou seg
            // Os que não pertencerem, adiciono a lista com o check desmarcado
            let temNaListaPrestadora: boolean;
            let temNaListaSeguradora: boolean;
            for (let i = 0; i < this.documentosFop.length; i++) {
                const docFop = this.documentosFop[i].documentofop;

                // Verificando se o documentofop está na lista de prestadora e de seguradora
                temNaListaPrestadora = this.temDocFopNaLista(docFop, this.documentosItensPrestadora);
                temNaListaSeguradora = this.temDocFopNaLista(docFop, this.documentosItensSeguradora);

                //Criando o documento item para colocar na lista de prestadora ou seguradora, caso o documento item ainda não esteja
                this.addDocsItensNaLista(this.documentosFop[i], temNaListaPrestadora, temNaListaSeguradora);
                
            }

            // Aqui, verifico se o template de email retornado na função já está dentro da configuração documento
            // Se já esta dentro, checked marcado, se não está, checked desmarcado
            let temTemplateEmail: boolean;
            if(this.templatesEmails.length > 0){

                for (let i = 0; i < this.templatesEmails.length; i++) {
                    const templateEmail = this.templatesEmails[i];

                    // Caso tenha template email na configuração, verifico se tem no array de templates de email 
                    if(this.atcConfiguracaoDocumento.atcsconfiguracoestemplatesemails.length > 0){

                        // Se houver, ele tem que aparecer marcado
                        for(let i = 0; i < this.atcConfiguracaoDocumento.atcsconfiguracoestemplatesemails.length; i++){
                            const templateEmailConfig = this.atcConfiguracaoDocumento.atcsconfiguracoestemplatesemails[i];
                            // if(templateEmail.atcconfiguracaotemplateemail == templateEmailConfig.atcconfiguracaotemplateemail){
                            //Verificando pelo código, considerando que não pode haver templates de email com mesmo código
                            if(templateEmail.codigo == templateEmailConfig.codigo){
                                temTemplateEmail = true;
                                break;
                            }
                            else{
                                temTemplateEmail = false;
                            }
                        }

                        this.addTemplateEmailTela(templateEmail, temTemplateEmail);

                    }

                    else{

                        this.addTemplateEmailTela(templateEmail, false);

                    }
                    
                }
            }
            this.busy = false;
            this.reloadScope();
        } catch(error){
            this.carregouSemErro = false;
            this.busy = false;
            this.reloadScope();
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao carregar a Configuração de Documento!'
            });
        }
    }

    /**
     * Verificando se o documentofop está no array passado
     * @param docFop 
     * @param arrayDocsItens 
     */
    private temDocFopNaLista(docFop, arrayDocsItens){
        let temNaLista: boolean;

        for (let i = 0; i < arrayDocsItens.length; i++) {
            const docAtual = arrayDocsItens[i].documentoitem.documentofop.documentofop;

            if(docFop == docAtual){
                temNaLista = true;
                break;
            }
            else{
                temNaLista = false;
            }
        }

        return temNaLista;
    }

    /**
     * Adiciona o documento item na lista de documentos itens da prestadora ou da seguradora
     */
    private addDocsItensNaLista(documentoFop, temNaListaPrestadora, temNaListaSeguradora){

        if(!temNaListaPrestadora){
            let configuracaoDocItem: IAtcConfiguracaoDocumentoItem = {
                documentofop: documentoFop,
                tipo: EnumTipoDocumentoItem.tdiPrestadora
            }

            let configuracaoDocItemTela: IAtcConfiguracaoDocumentoItemTela = {
                documentoitem: configuracaoDocItem,
                checked: false
            }

            this.documentosItensPrestadora.push(configuracaoDocItemTela);
            this.reloadScope();
        }

        if(!temNaListaSeguradora){
            let configuracaoDocItem: IAtcConfiguracaoDocumentoItem = {
                documentofop: documentoFop,
                tipo: EnumTipoDocumentoItem.tdiSeguradora
            }

            let configuracaoDocItemTela: IAtcConfiguracaoDocumentoItemTela = {
                documentoitem: configuracaoDocItem,
                checked: false
            }

            this.documentosItensSeguradora.push(configuracaoDocItemTela);
            this.reloadScope();
        }

    }

    /**
     * Adiciona o template de email na tela marcado ou desmarcado
     * @param templateEmail 
     * @param temTemplate 
     */
    private addTemplateEmailTela(templateEmail, temTemplate: boolean){

        if(!temTemplate){
            let templateEmailTela = {
                templateemail: templateEmail,
                checked: false
            }

            this.atcConfiguracaoDocumento.atcsconfiguracoestemplatesemails.push(templateEmail);
            this.templatesEmailsTela.push(templateEmailTela);
        }

        else{
            let templateEmailTela = {
                templateemail: templateEmail,
                checked: true
            }

            this.templatesEmailsTela.push(templateEmailTela);
        }

    }

    /**
     * Busca a configuração de documentos
     */
    private getConfiguracaoFromApi(): Promise<IAtcConfiguracaoDocumento> {
        return new Promise((resolve, reject) => {
            this.CrmAtcsconfiguracoesdocumentos.getListaFromApi().then((lista) => {
                if (lista.length > 0) {
                    this.CrmAtcsconfiguracoesdocumentos.get(lista[0].atcconfiguracaodocumento).then((dados: any) => {
                        resolve(dados);
                    }).catch((error) => {
                        reject(error);
                    });
                } else {
                    resolve(null);
                }
            }).catch((err) => {
                reject(err);
            });
        });
    }

    private carregarDocumentosFop() {
        return new Promise(async (resolve, reject) => {
          await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate('ns_documentosfop_index', {}, true, true),
            timeout: null
          })
            .then((response: any) => {
              resolve(response.data);
            })
            .catch((erro: any) => {
                reject(erro);
            });
        })
    }

    /**
     * Carrega o template de email padrão na tela
     */
    private carregarTemplatesEmail(){
        return new Promise((resolve, reject) => {
            this.CrmAtcsconfiguracoesdocumentos.getTemplatesEmail()
            .then((response: any) => {
                resolve(response);
            })
            .catch((err) => {
                reject(err);
            });
        });

    }

    uniqueValidation(params: any): any {
        if (this.collection) {
            let validation = { 'unique': true };
            for (var item in this.collection) {
                if (this.collection[item][params.field] === params.value) {
                    validation.unique = false;
                    break;
                }
            }
            return validation;
        } else {
            return null;
        }
    }

    reloadScope() {
        this.$scope.$applyAsync();
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid) {
            this.enviandoRequisicao = true;

            // antes de passar, tenho que limpar suas listas e depois atribuir novos valores a elas,
            // baseado no que está com o check marcado
            this.atcConfiguracaoDocumento.atcsconfiguracoesdocumentositens = [];
            this.atcConfiguracaoDocumento.atcsconfiguracoestemplatesemails = [];

            for (let i = 0; i < this.documentosItensPrestadora.length; i++) {
                const itemPrest = this.documentosItensPrestadora[i];


                if(itemPrest.checked == true){
                    this.atcConfiguracaoDocumento.atcsconfiguracoesdocumentositens.push(itemPrest.documentoitem);
                }

            }
            
            for (let i = 0; i < this.documentosItensSeguradora.length; i++) {
                const itemSeg = this.documentosItensSeguradora[i];

                if(itemSeg.checked == true){
                    this.atcConfiguracaoDocumento.atcsconfiguracoesdocumentositens.push(itemSeg.documentoitem);
                }

            }
            
            for (let i = 0; i < this.templatesEmailsTela.length; i++) {
                const tempEmail = this.templatesEmailsTela[i];

                if(tempEmail.checked == true){
                    this.atcConfiguracaoDocumento.atcsconfiguracoestemplatesemails.push(tempEmail.templateemail);
                }
            }

            this.CrmAtcsconfiguracoesdocumentos.save(this.atcConfiguracaoDocumento, true);
            this.reloadScope();
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }

    }

}

interface IAtcConfiguracaoDocumentoItemTela{
    documentoitem: IAtcConfiguracaoDocumentoItem;
    checked: boolean
}

interface IAtcConfiguracaoTemplateEmailTela{
    templateemail: IAtcConfiguracaoTemplateEmail;
    checked: boolean
}
