import angular = require('angular');
import { NsTiposdocumentos } from './../../../Ns/Tiposdocumentos/factory';
import { CrmAtcsdocumentos } from './../../Atcsdocumentos/factory';
import { CrmAtcstiposdocumentosrequisitantes } from './../../Atcstiposdocumentosrequisitantes/factory';
import moment = require('moment');
import { ISecaoController } from './../classes/isecaocontroller';
import { ILink, EnumTipoLink, CommonsUtilsService } from '../../../Commons/utils/utils.service';
import { CrmAtcTipoDocumentoRequisitante } from '../../Atcstiposdocumentosrequisitantes/classes/crmatctipodocumentorequisitante';
import { CrmAtcDocumento } from '../../Atcsdocumentos/classes/crmatcdocumento';
import { AtcDocumentoTela } from './classes/atcdocumentotela';
import { IConfigItemObjectList } from './../../../Commons/classes/iconfigitemobjectlist';
import { IAtcDocumentoTela } from './classes/iatcdocumentotela';
import { CrmAtcsDocumentosVisualizarModalService } from './visualizar/modal';
import { CrmAtcsDocumentosInformacoesAdicionaisModalService } from './informacoesadicionais/modal';
import { ECrmAtcDocumentoSituacao } from '../../Atcsdocumentos/classes/ecrmatcdocumentosituacao';
import { ICrmAtcDocumento } from '../../Atcsdocumentos/classes/icrmatcdocumento';
import { CrmModalAtcDocumentosLimparService } from './modal-limpar-documento/modal';
import { ModalExclusaoDocumentoService } from './modal-exclusao-documento';


/**
 * Sobreescrevendo para incluir campos customizados
 */
export class CrmAtcsDocumentosController {
    /**
     * Injeções de dependencia da tela
     */
    static $inject = [
        'entity',
        '$scope',
        'toaster',
        '$http',
        'nsjRouting',
        'CrmAtcsdocumentos',
        'NsTiposdocumentos',
        'CrmAtcstiposdocumentosrequisitantesFormService',
        'CrmAtcstiposdocumentosrequisitantes',
        '$rootScope',
        '$uibModal',
        'secaoctrl',
        'CommonsUtilsService',
        'CrmAtcsDocumentosVisualizarModalService',
        'CrmAtcsDocumentosInformacoesAdicionaisModalService',
        'CrmModalAtcDocumentosLimparService',
        'ModalExclusaoDocumentoService'
    ];

    private tipoDocumentoSelecionado: IAtcDocumentoTela;
    /**
     * Define se a tela está em processamento de inicialização
     */
    public busyInicializacao: boolean = false;
    /**
     * Define quantidade de skeletons que vai aparecer
     */
    public arrQtdSkeletons: any[] = [];
    private qtdSkeletons: number = 3;
    /**
     * Define se a tela está em processamento
     */
    public busyDocumentos: boolean = false;
    /**
     * Define mensagem de processamento
     */
    public busyMensagem: string = 'Carregando...';

    /**
     * Opções de dropzone
     */
    private options = {

        url: this.nsjRouting.generate('_uploader_upload_documentos', angular.extend({}), true),
        paramName: 'file',
        entidadeAtc: this.entity,
        renameFile: function (file: any) {

            let date = new Date();
            let randomId = '' + date.getFullYear() + date.getMonth() + date.getDate() + date.getHours() + date.getMinutes() + date.getSeconds() + date.getMilliseconds() +
                (Math.random() * 10).toString().replace('.', '') +
                file.name.substring(file.name.indexOf('.'), file.name.length);

            let atc = this.entidadeAtc;

            let newName = atc.tenant + ':' + atc.negocio + ':documentos:' + randomId;

            return newName;
        },
        // Função faz com que seja possível ter apenas um arquivo no dropzone. Se for adicionado mais de um, ele removerá o arquivo adicionado anteriormente no dropzone e manterá o mais recente
        init: function() {
            this.on('addedfile', function() {
              if (this.files.length > 1) {
                this.removeFile(this.files[0]);
              }
            });
        }, 
        maxFilesize: 5,
        maxFiles: 1,
        addRemoveLinks: true,
        thumbnailMethod: 'contain',
        dictDefaultMessage: 'Solte o arquivo aqui ou clique para adicionar um arquivo por vez',
        dictRemoveFile: 'Remover',
        dictCancelUpload: 'Aguarde',
        dictMaxFilesExceeded: 'Você não pode enviar mais arquivos',
        autoProcessQueue: false,
        uploadMultiple: false,
        parallelUploads: 1,
        dictCancelUploadConfirmation: 'Tem certeza de que deseja cancelar o upload do arquivo?'

    };

    private fileAdded = false;
    private envioHabilitado = false;

    /**
     * Objeto de configuração das colunas do object list de documentos
     */
    public configuracaoObjList = {
        image: null,
        nome: '',
        recebido: 'Recebido em',
        copiaexigida: 'Cópia exigida',
        aguardadopor: 'Aguardado por',
        solicitadopor: 'Solicitado por', 
        label: 'Situação',
        actions: null
    }
    /**
     * Define se a tela já foi inicializada
     */
    public inicializado: boolean = false;
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    /**
     * Lista de documentos apresentados na tela
     */
    public arrDocumentosTela: IAtcDocumentoTela[] = [];
    /**
     * Utilizado para enviar requisição de atc documento
     */
    public atcDocumentoParaEnviar: CrmAtcDocumento = null;

    /**
     * Faz o controle se o accordion de documentos já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    constructor(
        public entity: any,
        public $scope: any,
        public toaster: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public crmAtcsdocumentos: CrmAtcsdocumentos,
        public NsTiposdocumentos: NsTiposdocumentos,
        public CrmAtcstiposdocumentosrequisitantesFormService: any,
        public CrmAtcstiposdocumentosrequisitantes: CrmAtcstiposdocumentosrequisitantes,
        public $rootScope: any,
        public $uibModal: any,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController,
        public CommonsUtilsService: CommonsUtilsService,
        /**
         * Service de criação do modal de visualizar documento
         */
        public CrmAtcsDocumentosVisualizarModalService: CrmAtcsDocumentosVisualizarModalService,
        /**
         * Service de criação do modal de informações adicionais do documento
         */
        public CrmAtcsDocumentosInformacoesAdicionaisModalService: CrmAtcsDocumentosInformacoesAdicionaisModalService,
        /**
         * Service de criação do modal de limpar documentos
         */
        public CrmModalAtcDocumentosLimparService: CrmModalAtcDocumentosLimparService,

        //Modal de confirmação de exclusão do tipo de documento
        public ModalExclusaoDocumentoService: ModalExclusaoDocumentoService
    ) {
        this.onConfigItemLista = this.onConfigItemLista.bind(this);

        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
            }

            //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar dados do accordion
            if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                this.busyInicializacao = true;
                // Chamo função que carrega dados da tela;
                this.carregarDados();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
            this.busyInicializacao = false;
        }

        this.arrQtdSkeletons = [];
        for (let index = 0; index < this.qtdSkeletons; index++) {
            this.arrQtdSkeletons.push(index);
        }
    }

    /* Carregamento */
    Init(){
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;
            this.reloadScope();
        }
    }

    /**
     * Ativa o busy de processamento da tela.
     */
    private setBusy(mensagem = 'Carregando...'){
        this.busyMensagem = mensagem;
        this.busyDocumentos = true;
    }

    /**
     * Atualiza a visualização do escopo tela
     */
    reloadScope() {
        this.$scope.$applyAsync();
    }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDados(){

        this.busyDocumentos = true;

        try {
            // Busco dados da api
            const atc = this.entity.negocio;

            const [
                arrAtcsTiposDocumentosRequisitantes,
                arrAtcsDocumentos
            ] = await Promise.all([
                this.CrmAtcstiposdocumentosrequisitantes.getAll(atc),
                this.crmAtcsdocumentos.getAll({negocio: atc})
            ]);
            
            // Monto estrutura de dados utilizados pela tela
            this.arrDocumentosTela = [];

            arrAtcsTiposDocumentosRequisitantes.forEach((tipoDocReqForeach) => {
                // Monto estrutura inicial de dado da tela
                let documentoTela: IAtcDocumentoTela = {
                    atctipodocumentorequisitante: tipoDocReqForeach,
                    atcsdocumentos: []
                }

                let created_at = tipoDocReqForeach.get('created_at');
                tipoDocReqForeach.created_at = moment(created_at).toDate();

                // Adiciono documentos enviados ao atendimento, para cada tipo de documento requisitado
                arrAtcsDocumentos
                    .filter((atcDocFilter) => {
                        return atcDocFilter.tipodocumento.tipodocumento == tipoDocReqForeach.tipodocumento.tipodocumento;
                    }).forEach((atcDocForeach) => {
                        let doccreated_at = atcDocForeach.get('created_at');
                        atcDocForeach.created_at = moment(doccreated_at).toDate();

                        documentoTela.atcsdocumentos.push(atcDocForeach);
                    });

                // Adiciono objeto a lista de dados da tela
                this.arrDocumentosTela.push(documentoTela);
            });
        } catch (error) {}

        // Desativo loading
        this.busyInicializacao = false;
        this.busyDocumentos = false;

        //Uma vez que o accordion foi carregado, seto a variável de controle para true
        this.accordionCarregado = true;

        this.reloadScope();
    }

    /**
     * Evento chamado pelo nsj-object-list para cada item da lista para configurar apresentação e ações
     * @param dadoTela 
     */
    onConfigItemLista(documentoTela: IAtcDocumentoTela) {
        const documentoTelaObj = AtcDocumentoTela.getFromInterface(documentoTela);

        // Busco último documento enviado
        const ultimoDocumento = documentoTelaObj.ultimodocumento;

        // Monto objeto de configuração
        let entity = {
            nome: documentoTelaObj.atctipodocumentorequisitante.tipodocumento.nome,
            recebido: '-',
            copiaexigida: (documentoTelaObj.copiaexigida ? 'Sim' : 'Não'),
            aguardadopor: documentoTelaObj.requisitante,
            solicitadopor: '-'
        }
        
        // Preencho recebido
        if (ultimoDocumento != null) {
            entity.recebido = this.getDataFormatada(ultimoDocumento.created_at);
        }

        // Preencho solicitador por
        entity.solicitadopor = documentoTelaObj.atctipodocumentorequisitante.created_by.nome
            + ' em ' + this.getDataFormatada(documentoTelaObj.atctipodocumentorequisitante.created_at)
            + ' às ' + this.getDataFormatada(documentoTelaObj.atctipodocumentorequisitante.created_at, 'HH:mm:ss');

        let configuracao: IConfigItemObjectList = {
            entity: entity,
            label: {
                text: documentoTelaObj.statusDescricao,
                color: documentoTelaObj.statusClasseLabel,
            },
            image: {
                link: '',
                src: (ultimoDocumento != null) ? ultimoDocumento.url : null
            },
            actions: []
        };

        // Configuro ações
        // Se algum documento já foi enviado
        if (ultimoDocumento != null) {
            // Baixar
            configuracao.actions.push({
                label: 'Baixar',
                icon: 'fas fa-download fa-fw',
                method: (documentoTela: IAtcDocumentoTela, index: any) => {
                    const documentoTelaObj = AtcDocumentoTela.getFromInterface(documentoTela);
                    this.onBaixarDocumentoClick(documentoTelaObj);
                }
            });
            // Visualizar
            configuracao.actions.push({
                label: 'Visualizar',
                icon: 'far fa-eye',
                method: (documentoTela: IAtcDocumentoTela, index: any) => {
                    const documentoTelaObj = AtcDocumentoTela.getFromInterface(documentoTela);
                    this.onVisualizarDocumentoClick(documentoTelaObj);
                }
            });
            // Imprimir
            configuracao.actions.push({
                label: 'Imprimir',
                icon: 'fas fa-print fa-fw',
                method: (documentoTela: IAtcDocumentoTela, index: any) => {
                    const documentoTelaObj = AtcDocumentoTela.getFromInterface(documentoTela);
                    this.onImprimirDocumentoClick(documentoTelaObj);
                }
            });
            // Limpar documento
            configuracao.actions.push({
                label: 'Limpar Documento',
                icon: 'fas fa-eraser',
                method: (documentoTela: AtcDocumentoTela, index: any) => {
                    const documentoTelaObj = AtcDocumentoTela.getFromInterface(documentoTela);
                    this.onLimparDocumentoClick(documentoTelaObj);
                }
            });
        }

        // Excluir
        configuracao.actions.push({
            label: 'Excluir',
            icon: 'far fa-trash-alt',
            method: (documentoTela: AtcDocumentoTela, index: any) => {
                const documentoTelaObj = AtcDocumentoTela.getFromInterface(documentoTela);

                //Status do documento onde não é permitida a exclusão. 2 - Pré aprovado, 4 - Aprovado, 5 - Recusado
                let status = [2, 4, 5];

                //Verificando se o status do tipo de documento é algum desses definido no array
                let naoPodeExcluir = status.some((valorStatus) => {
                    return valorStatus == documentoTelaObj.status;
                });

                //Se não puder excluir, mostro toaster informando
                if(naoPodeExcluir){

                    let statusDescricao: string;
                    let msgToaster: string;

                    //Descrição que será mostrada no toaster
                    switch (documentoTelaObj.status) {
                        case 2: {
                            statusDescricao = 'Pré-Aprovado';
                            break;
                        }
                        case 4: {
                            statusDescricao = 'Aprovado';
                            break;
                        }
                        case 5: {
                            statusDescricao = 'Recusado';
                            break;
                        }
                    }

                    //Montando mensagem exibida no toaster
                    msgToaster = "Não é possível excluir um Tipo de Documento " + statusDescricao;

                    this.toaster.pop({
                        type: 'error',
                        title: msgToaster
                    });

                    this.reloadScope();
                    return;
                    
                }

                //Chamar função que abrirá a modal de confirmação
                let modal = this.ModalExclusaoDocumentoService.open(documentoTelaObj);

                modal.result.then((response: any) => {

                    // Se não confirmar a exclusão, simplesmente retorno
                    if(response == 'manter'){
                        return;
                    }
                    // Caso confirme a exclusão, chamo a função de excluir
                    else{ 
                        this.onExcluirDocumentoClick(documentoTelaObj);
                    }
    
                })
                .catch((error: any) => {
                    if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                        this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                    }
                });

            }
        });

        return configuracao;
    }

    /**
     * Retorna data formatada com data e hora
     * @param data 
     */
    getDataFormatada(data: Date, formato: string = 'DD/MM/YYYY'): string {
        if (data != null) {
            return this.CommonsUtilsService.getDataFormatada(data.toISOString(), formato);
        } else {
            return '-';
        }
    }

    /**
     * Evento disparado ao clicar no botão de Solicitar novo tipo de documento
     */
    onSolicitarNovoTipoDocumentoClick() {
        this.CrmAtcstiposdocumentosrequisitantes.constructors = {
            negocio: this.entity.negocio
        };
        let modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open({}, {});
        modal.result.then((subentity: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O novo Tipo de Documento foi solicitado com sucesso!'
            });

            // Defino estrutura do novo tipo de documento
            let documentoTela: IAtcDocumentoTela = {
                atctipodocumentorequisitante: CrmAtcTipoDocumentoRequisitante.getFromItemApi(subentity),
                atcsdocumentos: []
            }
            documentoTela.atctipodocumentorequisitante.created_at = moment(subentity.created_at).toDate();

            // Adiciono novo tipo de documento a lista
            this.arrDocumentosTela = [...this.arrDocumentosTela, documentoTela];
        })
        .catch((error: any) => {
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }

    /**
     * Evento chamado ao clicar no botão de enviar documento
     */
    onEnviarDocumento() {
        // Se já enviou o arquivo pro S3, só chamo requisição para salvar negócio documento
        this.atcDocumentoParaEnviar = new CrmAtcDocumento({});
        this.atcDocumentoParaEnviar.negocio = {
            negocio: this.entity.negocio
        };
        this.atcDocumentoParaEnviar.status = ECrmAtcDocumentoSituacao.cadsRecebido;
        this.atcDocumentoParaEnviar.tipodocumento = this.tipoDocumentoSelecionado.atctipodocumentorequisitante.tipodocumento;

        // Se precisa de mais informações, abro modal de mais informações
        if (
            this.tipoDocumentoSelecionado.atctipodocumentorequisitante.pedirinformacoesadicionais
            && this.tipoDocumentoSelecionado.atctipodocumentorequisitante.tipodocumento.prestador != null
            && this.tipoDocumentoSelecionado.atctipodocumentorequisitante.tipodocumento.prestador != ''
        ) {
            const modal = this.CrmAtcsDocumentosInformacoesAdicionaisModalService.open({atc: this.entity.negocio}, this.tipoDocumentoSelecionado);
    
            modal.result.then((dados: CrmAtcDocumento)=>{
                // Preencho informações extras
                this.atcDocumentoParaEnviar.tiponf = dados.tiponf;
                this.atcDocumentoParaEnviar.seriedocumento = dados.seriedocumento;
                this.atcDocumentoParaEnviar.numerodocumento = dados.numerodocumento;
                this.atcDocumentoParaEnviar.emissaodocumento = dados.emissaodocumento;
                this.atcDocumentoParaEnviar.orcamentos = dados.orcamentos;

                this.setBusy('Enviando documento...');
                this.$scope.$broadcast('processQueue');
            }).catch((error)=>{
                if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao tentar preencher informações adicionais do documento.'
                    });
                }
            });
        } else {
            this.setBusy('Enviando documento...');
            this.$scope.$broadcast('processQueue');
        }
    }

    /**
     * Envia a requisição de criar o atc documento
     */
    criarAtcDocumento(){
        this.crmAtcsdocumentos.create(this.atcDocumentoParaEnviar.getDados()).then((atcDocumentoCriado: ICrmAtcDocumento) => {
            // Apresento mensagem de sucesso.
            this.toaster.pop({
                type: 'success',
                title: 'O Documento foi enviado com sucesso!'
            });

            // Removo arquivo do dropzone
            this.removerArquivo();

            // Instancio objeto de atcdocumento a partir da interface
            const atcDocumento = CrmAtcDocumento.getFromItemApi(atcDocumentoCriado);
            let doccreated_at = atcDocumento.get('created_at');
            atcDocumento.created_at = moment(doccreated_at).toDate();

            // Busco documento tela da lista
            let documentoTelaLista = this.arrDocumentosTela.find((docTelaFind) => {
                return docTelaFind.atctipodocumentorequisitante.tipodocumento.tipodocumento == atcDocumento.tipodocumento.tipodocumento;
            });
            // Adiciono objeto de atc documento a lista
            let documentoAtcIndex = documentoTelaLista.atcsdocumentos.findIndex((docAtcFindIndex) => {
                return docAtcFindIndex.negociodocumento == atcDocumento.negociodocumento;
            });

            if (documentoAtcIndex > -1) {
                documentoTelaLista.atcsdocumentos[documentoAtcIndex] = atcDocumento;
            } else {
                documentoTelaLista.atcsdocumentos.push(atcDocumento);
            }
            // Forço atualização da lista de documentos da tela
            this.arrDocumentosTela = [...this.arrDocumentosTela];
        }).catch((err) => {
            if (err != null && err.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: err.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: "Ocorreu um erro ao criar o documento!"
                });
            }
        }).finally(() => {
            this.busyDocumentos = false;
            this.reloadScope();
        });
    }

    /**
     * Habilita botão de envio do documento
     */
    habilitarDesabilitarEnvio() {
        this.envioHabilitado = (this.fileAdded && this.tipoDocumentoSelecionado != null);
        this.reloadScope();
    }

    /**
     * Verifica se o envio do documento está habilitado
     */
    isEnvioHabilitado(): boolean {
        return this.envioHabilitado;
    }

    /**
     * Verifica se a tela de documentos está em processamento
     */
    isBusyDocumentos() {
        return this.busyDocumentos;
    }
    
    /** FUNÇÕES DE DROPZONE */
    
    /**
     * Remove arquivo do objeto do Dropzone
     */
    removerArquivo() {
        if (this.fileAdded) {
            this.$scope.$broadcast('removeFile');
        }
    }

    /**
     * Evento de adicionar arquivo ao dropzone
     * @param file 
     */
    addFile(file: any) {
        this.fileAdded = true;
        this.habilitarDesabilitarEnvio();
    }
    
    /**
     * Evento de remover arquivo do dropzone
     * @param file 
     */
    removeFile(file: any) {
        this.fileAdded = false;
        this.habilitarDesabilitarEnvio();
    }
    
    /**
     * 
     * @param response
     * @todo Verificar se o response é o mesmo quando o upload é cancelado manualmente e quando é cancelado por um erro do sistema, para exibir uma mensagem adequada quando ocorrer um erro do sistema
     */
    errorFile(response: any) {
        this.busyDocumentos = false;
    }
    
    /**
     * Evento chamado quando arquivo do dropzone realizar o upload com sucesso
     * @param response 
     */
    successFile(response: any) {
        // Preencho informações de URL e Mime Type no documento a ser enviado
        this.atcDocumentoParaEnviar.url = response.s3key;
        this.atcDocumentoParaEnviar.tipomime = response.file.type;

        // Chamo função de criar documento
        this.criarAtcDocumento();
    }

    /** FUNÇÕES DE DROPZONE - FIM */

    /**
     * Retorna o html utilizado na página de impressão
     * @param url 
     * @param title 
     */
    getHtmlImpressao(url: any, title: string = 'atcs') {
        return `
            <html>
                <head>
                    <title>${title}</title>
                    <!-- Funcoes -->
                    <script>
                        <!-- Chama o passo 2 após 10 milisegundos  -->
                        function passo1(){
                            setTimeout(() => {
                                passo2();
                            }, 10);
                        }

                        <!-- Chama impressão do navegador -->
                        function passo2(){
                            <!-- Chama impressão -->
                            window.print();
                            <!-- Fecha a tela -->
                            window.close();
                        }
                    </script>
                </head>
                <body onload="passo1()">
                    <!-- Imagem a ser impressa -->
                    <img style="width: 100%;" src="${url}"/>
                </body>
            </html>
        `;
    }

    /** AÇÕES DO OBJECT LIST */

    /**
     * Evento disparado ao clicar na ação de baixar documento
     */
    onBaixarDocumentoClick(documentoTela: AtcDocumentoTela){
        this.setBusy('Baixando documento...');
        this.crmAtcsdocumentos.baixarDocumento(documentoTela.ultimodocumento.negociodocumento).then((response) => {
            // Dados para geração do link
            let link: ILink = {
                nome: documentoTela.atctipodocumentorequisitante.tipodocumento.nome,
                binary: response
            }

            this.CommonsUtilsService.baixarFromLink(link, EnumTipoLink.tlBinary);
        }).catch((response) => {
            if (response != null && (response.data) != null && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao baixar documento.'
                });
            }
        }).finally(() => {
            this.busyDocumentos = false;
            this.reloadScope();
        });
    }

    /**
     * Evento disparado ao clicar na ação de imprimir documento
     */
    onImprimirDocumentoClick(documentoTela: AtcDocumentoTela){
        const nomeDocumento = documentoTela.atctipodocumentorequisitante.tipodocumento.nome;
        
        var Pagelink = 'about:blank';
        var pwa = window.open(Pagelink, '_new');
        pwa.document.open();
        pwa.document.title = nomeDocumento;
        pwa.document.write( this.getHtmlImpressao(documentoTela.ultimodocumento.url, nomeDocumento) );
        pwa.document.close();
    }

    /**
     * Evento disparado ao clicar na ação de visualizar documento
     */
    onVisualizarDocumentoClick(documentoTela: AtcDocumentoTela){
        const modal = this.CrmAtcsDocumentosVisualizarModalService.open({}, documentoTela.toInterface());
        
        modal.result.then((atcDocumentoAnalise: CrmAtcDocumento)=>{
            this.setBusy('Enviando análise do documento...');
            this.crmAtcsdocumentos.preAnalise(atcDocumentoAnalise.getDados()).then(() => {
                // Apresento mensagem de sucesso.
                this.toaster.pop({
                    type: 'success',
                    title: 'Análise do documento enviada com sucesso!'
                });
    
                // Busco documento tela da lista
                let documentoTelaLista = this.arrDocumentosTela.find((docTelaFind) => {
                    return docTelaFind.atctipodocumentorequisitante.tipodocumento.tipodocumento == atcDocumentoAnalise.tipodocumento.tipodocumento;
                });
                // Adiciono objeto de atc documento a lista
                let documentoAtcIndex = documentoTelaLista.atcsdocumentos.findIndex((docAtcFindIndex) => {
                    return docAtcFindIndex.negociodocumento == atcDocumentoAnalise.negociodocumento;
                });
    
                if (documentoAtcIndex > -1) {
                    documentoTelaLista.atcsdocumentos[documentoAtcIndex] = atcDocumentoAnalise;
                } else {
                    documentoTelaLista.atcsdocumentos.push(atcDocumentoAnalise);
                }
                // Forço atualização da lista de documentos da tela
                this.arrDocumentosTela = [...this.arrDocumentosTela];
            }).catch((err) => {
                if (err != null && err.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: err.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: "Ocorreu um erro ao enviar análise do documento!"
                    });
                }
            }).finally(() => {
                this.busyDocumentos = false;
                this.reloadScope();
            })
        }).catch((error)=>{
            if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao tentar abrir visualização do documento.'
                });
            }
        });
    }

    /**
     * Evento disparado ao clicar na ação de limpar documento
     * Exclui o último atcdocumento enviado para o tipo de documento requisitante
     */
    onLimparDocumentoClick(documentoTela: AtcDocumentoTela){
        const modal = this.CrmModalAtcDocumentosLimparService.open({atc: this.entity.negocio}, documentoTela.toInterface());
    
        modal.result
            .then(()=>{})
            .catch((error)=>{
                if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao tentar abrir tela.'
                    });
                }
            })
            .finally(() => {
                this.busyDocumentos = true;
                // Chamo função que carrega dados da tela;
                this.carregarDados();
            });
    }

    /**
     * Evento disparado ao clicar na ação de excluir documento
     * Exclui o documento requisitante e os atcsdocumentos enviados para este tipo de documento, caso existam
     */
    async onExcluirDocumentoClick(documentoTela: AtcDocumentoTela){
        this.setBusy('Excluindo documento...');

        // Exclui atcsdocumentos
        let reqs = {
            arrPromisses: []
        }
        for (let index = 0; index < documentoTela.atcsdocumentos.length; index++) {
            reqs.arrPromisses.push(
                this.crmAtcsdocumentos.excluir(documentoTela.atcsdocumentos[index].negociodocumento)
            );
        }

        // Envio requisições de exclusão dos atcs documentos
        try {
            await Promise.all(reqs.arrPromisses);
        } catch (error) {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar excluir Tipo de Documento do atendimento.'
            });
            this.busyDocumentos = false;
            this.reloadScope();
            return;
        }

        // Envio requisição de exclusão do atctipodocumentorequisitante do atendimento
        try {
            await this.CrmAtcstiposdocumentosrequisitantes.excluir(
                documentoTela.atctipodocumentorequisitante.negociotipodocumentorequisitante, 
                this.entity.negocio
            );

            // Apresento mensagem de sucesso
            this.toaster.pop({
                type: 'success',
                title: 'O Tipo de Documento foi excluído do atendimento com sucesso!'
            });

            // Removo item da lista
            const documentoIndex = this.arrDocumentosTela.findIndex((docFindIndex) => {
                return docFindIndex.atctipodocumentorequisitante.negociotipodocumentorequisitante == documentoTela.atctipodocumentorequisitante.negociotipodocumentorequisitante;
            });
            this.arrDocumentosTela.splice(documentoIndex, 1);

            // Forço atualização da lista de documentos da tela
            this.arrDocumentosTela = [...this.arrDocumentosTela];

            // Removo loading
            this.busyDocumentos = false;
            this.reloadScope();
        } catch (error) {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar excluir Tipo de Documento do atendimento.'
            });
            this.busyDocumentos = false;
            this.reloadScope();
            return;
        }
    }

    /** AÇÕES DO OBJECT LIST - FIM */
}
