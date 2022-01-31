import angular = require('angular');
import { NsTiposdocumentos } from './../../Ns/Tiposdocumentos/factory';
import { CrmAtcsdocumentos } from './../Atcsdocumentos/factory';
import { CrmAtcstiposdocumentosrequisitantes } from './../Atcstiposdocumentosrequisitantes/factory';
import moment = require('moment');
import { ISecaoController } from './classes/isecaocontroller';
import { ILink, EnumTipoLink, CommonsUtilsService } from '../../Commons/utils/utils.service';

/**
 * Sobreescrevendo para incluir campos customizados
 */
export class CrmAtcsDocumentosController {

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
        'CommonsUtilsService'
    ];

    public camposcustomizados: any = {};
    public atcsfilhos: any = [];
    private tipoDocumentoSelecionado;
    /**
     * Define se a tela está em processamento de inicialização
     */
    public busyInicializacao: boolean = false;
    /**
     * Define se a tela está em processamento
     */
    public busyDocumentos: boolean = false;
    /**
     * Define mensagem de processamento
     */
    public busyMensagem: string = 'Carregando...';

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
        maxFilesize: 5,
        maxFiles: 1,
        addRemoveLinks: true,
        thumbnailMethod: 'contain',
        dictDefaultMessage: 'Solte o arquivo aqui ou clique para adicionar',
        dictRemoveFile: 'Remover',
        dictCancelUpload: 'Aguarde',
        dictMaxFilesExceeded: 'Você não pode enviar mais arquivos',
        autoProcessQueue: false,
        uploadMultiple: false,
        parallelUploads: 1,
        dictCancelUploadConfirmation: 'Tem certeza de que deseja cancelar o upload do arquivo?'

    };

    private documentos = [];
    private documentosParaListar: any[];
    private listaTiposDocumentos = [];
    private fileAdded = false;
    private fileUrl = "";
    private documentoParaAlterar;
    private envioHabilitado;
    private mensagemExibida = true;
    private typeMime = "";

    public configObjectlist = {
        image: null,
        nome: '',
        recebido: 'Recebido em',
        copiaexigida: 'Cópia exigida',
        aguardadopor: 'Aguardado por',
        solicitadopor: 'Solicitado por', 
        label: 'Situação',
        actions: null
    }

    private documentStyle = [
        { status: 0, texto: 'NÃO ENVIADO', cor: 'label-default', icone: 'fas fa-download' },
        { status: 1, texto: 'RECEBIDO', cor: 'label-warning', icone: 'fas fa-exclamation' },
        { status: 2, texto: 'PRÉ-APROVADO', cor: 'label-success', icone: 'fas fa-check' },
        { status: 3, texto: 'ENVIADO PARA REQUISITANTE', cor: 'label-primary', icone: 'fas fa-eye' },
        { status: 4, texto: 'APROVADO', cor: 'label-success', icone: 'fas fa-check' },
        { status: 5, texto: 'RECUSADO', cor: 'label-danger', icone: 'fas fa-times' },
    ];

    private tiposRequisitantes = [
        { cod: 0, valor: 'Negócio' },
        { cod: 1, valor: 'Cliente' },
        { cod: 2, valor: 'Template da Apólice' },
        { cod: 3, valor: 'Fornecedor' },
    ];
    public constructors: any = {} ;
    public inicializado: boolean = false;
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

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
        public CommonsUtilsService: CommonsUtilsService
    ) {
        this.montaListaDocumentos = this.montaListaDocumentos.bind(this);

        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
            }

            if (!this.atualizacaoAtivada && this.inicializado) {
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                // Chamo função que carrega dados da tela;
                this.carregarDocumentos();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }
    }

    /* Carregamento */
    Init(){
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;

            this.carregarListaTiposDocumentos();
            this.constructors.negocio =  this.entity.negocio;
            this.listenDocumentosSalvosComSucesso();
            
            // Evento: Adicionar documento ao negócio - Sucesso
            this.$scope.$on('crm_atcsdocumentos_submitted', () => {
                this.toaster.pop({
                    type: 'success',
                    title: 'O Documento foi enviado com sucesso!'
                });
                this.removerArquivo();
                this.carregarDocumentos();
            });
    
            // Evento: Adicionar documento ao negócio - Erro
            this.$scope.$on('crm_atcsdocumentos_submit_error', (event: any, args: any) => {
                this.toaster.pop({
                    type: 'error',
                    title: args.response.data.message
                });
                this.busyDocumentos = false;
            });
    
            // Evento: Exclusão do tipo de documento requerido do negócio - Sucesso
            this.$scope.$on('crm_atcstiposdocumentosrequisitantes_deleted', () => {
                this.toaster.pop({
                    type: 'success',
                    title: 'O Tipo de Documento foi excluído do atendimento com sucesso!'
                });
                this.entity.negociostiposdocumentosrequisitantes = this.CrmAtcstiposdocumentosrequisitantes.reload();
            });
        
            // Evento: Exclusão do tipo de documento requerido do negócio - Erro
            this.$scope.$on('crm_atcstiposdocumentosrequisitantes_delete_error', (event: any, args: any) => {
                this.toaster.pop({
                    type: 'error',
                    title: args.response.data.message
                });
            });
    
            // Evento: Carregou lista de tipos de documentos requeridos do negócio
            this.$scope.$on('crm_atcstiposdocumentosrequisitantes_list_finished', () => {
                this.atualizarListaDocumentosEsperados();
            });
        
            // Evento: Exclusão de documento do negócio - Sucesso
            this.$scope.$on('crm_atcsdocumentos_deleted', (event: any, args: any) => {
                this.carregarDocumentos();
            });
        
            // Evento: Exclusão de documento do negócio - Erro
            this.$scope.$on('crm_atcsdocumentos_delete_error', (event: any, args: any) => {
                this.toaster.pop({
                    type: 'error',
                    title: args.response.data.message
                });
            });
    
            // Evento: Carregou lista de documentos
            this.$scope.$on('crm_atcsdocumentos_list_finished', () => {
                this.atualizarListaDocumentosEsperados();
            });
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

    listenDocumentosSalvosComSucesso() {
        this.$rootScope.$on('crm_atcsdocumentos_submitted', () => {
            if (!this.mensagemExibida) {
                this.toaster.pop({
                    type: 'success',
                    title: 'Documento salvos com sucesso!'
                });
        
                this.mensagemExibida = true;
        
                this.removerArquivo();
        
                this.carregarDocumentos();
            }
        });
    }
    
    // Não sei quando é chamado
    montaListaDocumentos(tipodocumento: any) {
        let nome = tipodocumento.documento.tipodocumento ? tipodocumento.documento.tipodocumento.nome : null;

        const aux = {
            entity: {
                nome: nome != null ? nome : tipodocumento.nome,
                recebido: tipodocumento.documento.created_at ? moment(tipodocumento.documento.created_at).format('DD/MM/YYYY') : '-',
                aguardadopor: this.getRequisitante(tipodocumento),
                solicitadopor: this.getSolicitadoPor(tipodocumento),
                copiaexigida: this.getCopiaExigida(tipodocumento),
            },
            label: {
                color: tipodocumento.documento.tipodocumento ? this.getDocumentStyle(tipodocumento.documento).cor : this.documentStyle[0].cor,
                text: tipodocumento.documento.tipodocumento ?  this.getDocumentStyle(tipodocumento.documento).texto : this.documentStyle[0].texto,
            },
            actions: tipodocumento.documento.tipodocumento ? [
                {
                    label: 'Baixar',
                    icon: 'fas fa-download fa-fw',
                    method: (tipodocumento: any, index: any) => {
                        this.baixarDocumento(tipodocumento.documento.negociodocumento, tipodocumento.nome);
                    }
                },
                {
                    label: 'Visualizar',
                    icon: 'far fa-eye',
                    method: (tipodocumento: any, index: any) => {
                        this.itemClicked(tipodocumento.documento)
                    }
                },
                {
                    label: 'Imprimir',
                    icon: 'fas fa-print fa-fw',
                    method: (tipodocumento: any, index: any) => {
                        this.printImage(tipodocumento.documento)
                    }
                },
                {
                    label: 'Limpar Documento',
                    icon: 'fas fa-eraser',
                    method: (tipodocumento: any, index: any) => {
                        this.apagarDocumento(tipodocumento.documento.negociodocumento, index);
                    }
                },
                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (tipodocumento: any, index: any) => {
                        this.excluirTipoDocumentoRequisitanteAtc(tipodocumento);
                    }
                }
            ] : [
                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (tipodocumento: any, index: any) => {
                        this.excluirTipoDocumentoRequisitanteAtc(tipodocumento);
                    }
                }
            ],
            image: {
                link: '',
                src: tipodocumento.documento.status > 0 ? tipodocumento.documento.url : null,
            },
        }
        return aux;
    }
    
    async carregarDocumentos() {
        this.busyDocumentos = true;
        this.documentos = [];
        this.crmAtcsdocumentos.constructors = [];
        this.crmAtcsdocumentos.constructors.id = (<any>this).entity.negocio;
        
        await this.$http.get(this.nsjRouting.generate('crm_atcstiposdocumentosrequisitantes_index', { 'negocio': (<any>this).entity.negocio })).then((response) => {
            (<any>this).entity.negociostiposdocumentosrequisitantes = response.data as any;;
        });
        
        await this.$http.get(this.nsjRouting.generate('crm_atcsdocumentos_index', { 'negocio': (<any>this).entity.negocio })).then((response) => {
            this.documentos = response.data as any;
            this.atualizarListaDocumentosEsperados();
            this.busyDocumentos = false;
        });
    }

           
    atualizarListaDocumentosEsperados() {
        this.documentosParaListar = this.montarListaTiposDocumentosRequeridos(this.documentos);
    }
    
    montarListaTiposDocumentosRequeridos(documentos: any){
        let tiposdocumentos = [];
        for(var i = 0; i < this.entity.negociostiposdocumentosrequisitantes.length; i++){
            if(!this.listaPossuiTipoDocumento(tiposdocumentos, this.entity.negociostiposdocumentosrequisitantes[i].tipodocumento.tipodocumento)){
                tiposdocumentos.push(angular.copy(this.entity.negociostiposdocumentosrequisitantes[i].tipodocumento));
            }
        }
        for(var j = 0; j < tiposdocumentos.length; j++){
            tiposdocumentos[j].documento = this.getDocumentoByTipoDocumento(documentos, tiposdocumentos[j].tipodocumento);
        }
        return tiposdocumentos;
    }
    
    listaPossuiTipoDocumento(lista: any, tipodocumento: any){
        for(var i = 0; i < lista.length; i++){
            if(lista[i].tipodocumento == tipodocumento) return true;
        }
        return false;
    }
    
    getDocumentoByTipoDocumento(documentos: any, tipodocumento: any){
        for(var i = 0; i < documentos.length; i++){
            if(documentos[i].tipodocumento.tipodocumento == tipodocumento){
                return documentos[i];
            }
        }
        return {};
    }
    
    crmAtcstiposdocumentosrequisitantesForm() {
        this.CrmAtcstiposdocumentosrequisitantes.constructors = {'negocio':this.entity.negocio};
        let modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open({}, {});
        modal.result.then((subentity: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O novo Tipo de Documento foi solicitado com sucesso!'
            });
            if (this.entity.negociostiposdocumentosrequisitantes === undefined) {
                this.entity.negociostiposdocumentosrequisitantes = [subentity];
            } else {
                this.entity.negociostiposdocumentosrequisitantes.push(subentity);
            }
            this.atualizarListaDocumentosEsperados();
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
    
    carregarListaTiposDocumentos() {
        this.listaTiposDocumentos = this.NsTiposdocumentos.reload();
    }
    
    getDocumentStyle(documento: any) {
        return this.documentStyle.find(o => o.status === documento.status);
    }
    
    getRequisitante(tipodocumento: any) {
        let requisitantes = [];
        if(tipodocumento){
            for(let i = 0; i < this.entity.negociostiposdocumentosrequisitantes.length; i++){
                if(tipodocumento.tipodocumento == this.entity.negociostiposdocumentosrequisitantes[i].tipodocumento.tipodocumento){
                    if(this.entity.negociostiposdocumentosrequisitantes[i].requisitantecliente 
                        && this.entity.negociostiposdocumentosrequisitantes[i].requisitantecliente.cliente 
                        && !requisitantes.includes('Cliente')
                    ){
                        requisitantes.push('Cliente');
                    } else if (this.entity.negociostiposdocumentosrequisitantes[i].requisitantefornecedor 
                        && this.entity.negociostiposdocumentosrequisitantes[i].requisitantefornecedor.fornecedor 
                        && !requisitantes.includes('Fornecedor')
                    ) {
                        requisitantes.push('Fornecedor');
                    } else if(this.entity.negociostiposdocumentosrequisitantes[i].requisitanteapolice
                        && this.entity.negociostiposdocumentosrequisitantes[i].requisitanteapolice.templateproposta 
                        && !requisitantes.includes('Apólice')
                    ){
                        requisitantes.push('Apolice');
                    } else if (this.entity.negociostiposdocumentosrequisitantes[i].requisitantenegocio 
                        && !requisitantes.includes('Atendimento Comercial')
                    ) {
                        requisitantes.push('Atendimento Comercial');
                    }
                }
            }
        } else {
            requisitantes.push('-');
        }
        return requisitantes.sort().join('/');
    }

    getSolicitadoPor(tipodocumento: any) {
        let solicitadoPor = '';
        let usuario = {};
        if(tipodocumento){
            for(let i = 0; i < this.entity.negociostiposdocumentosrequisitantes.length; i++){
                if ( (tipodocumento.tipodocumento === this.entity.negociostiposdocumentosrequisitantes[i].tipodocumento.tipodocumento) &&
                     (this.entity.negociostiposdocumentosrequisitantes[i].requisitantenegocio) ) {
                        if( typeof (this.entity.negociostiposdocumentosrequisitantes[i].created_by) === 'object') {
                            usuario = this.entity.negociostiposdocumentosrequisitantes[i].created_by;
                        } else {
                            usuario = JSON.parse(this.entity.negociostiposdocumentosrequisitantes[i].created_by);
                        }
                        solicitadoPor = usuario.nome + ' em ' +  
                                        moment(this.entity.negociostiposdocumentosrequisitantes[i].created_at).format('DD/MM/YYYY') + 
                                        ' às ' + moment(this.entity.negociostiposdocumentosrequisitantes[i].created_at).format('HH:mm:ss');
                        break;
                }
            }
        } else {
            solicitadoPor = '-';
        }
        return solicitadoPor;
    }

    getCopiaExigida(tipodocumento: any) {
        let copiaExigida = false;
        if(tipodocumento){
            for(let i = 0; i < this.entity.negociostiposdocumentosrequisitantes.length; i++){
                if ( (tipodocumento.tipodocumento === this.entity.negociostiposdocumentosrequisitantes[i].tipodocumento.tipodocumento) ) {
                    copiaExigida = ( (this.entity.negociostiposdocumentosrequisitantes[i].copiasimples) || 
                                     (this.entity.negociostiposdocumentosrequisitantes[i].copiaautenticada) )
                    break;
                }
            }
        } else {
            copiaExigida = false;
        }
        return copiaExigida ? 'Sim' : 'Não';
    }
    
    getCopiasExigidas(tipodocumento: any){
        let copiasimples = [];
        let copiaautenticada = [];
        let original = [];
        let enviarporemail = false;
        for(let i = 0; i < this.entity.negociostiposdocumentosrequisitantes.length; i++){
            if(this.entity.negociostiposdocumentosrequisitantes[i].tipodocumento.tipodocumento == tipodocumento.tipodocumento){
                let documentoesperado = this.entity.negociostiposdocumentosrequisitantes[i];

                if (documentoesperado.permiteenvioemail) {
                    enviarporemail = true;
                }
                if(documentoesperado.requisitantecliente && documentoesperado.requisitantecliente.cliente){
                    if(documentoesperado.copiasimples && !copiasimples.includes('Cliente')){
                        copiasimples.push('Cliente');
                    }
                    if(documentoesperado.copiaautenticada && !copiaautenticada.includes('Cliente')){
                        copiaautenticada.push('Cliente')
                    }
                    if(documentoesperado.original && !original.includes('Cliente')){
                        original.push('Cliente');
                    }
                }
                if(documentoesperado.requisitantefornecedor && documentoesperado.requisitantefornecedor.fornecedor){
                    if(documentoesperado.copiasimples && !copiasimples.includes('Fornecedor')){
                        copiasimples.push('Fornecedor');
                    }
                    if(documentoesperado.copiaautenticada && !copiaautenticada.includes('Fornecedor')){
                        copiaautenticada.push('Fornecedor')
                    }
                    if(documentoesperado.original && !original.includes('Fornecedor')){
                        original.push('Fornecedor');
                    }
                }
                if(documentoesperado.requisitanteapolice && documentoesperado.requisitanteapolice.templateproposta){
                    if(documentoesperado.copiasimples && !copiasimples.includes('Apólice')){
                        copiasimples.push('Apólice');
                    }
                    if(documentoesperado.copiaautenticada && !copiaautenticada.includes('Apólice')){
                        copiaautenticada.push('Apólice')
                    }
                    if(documentoesperado.original && !original.includes('Apólice')){
                        original.push('Apólice');
                    }
                }
                if(documentoesperado.requisitantenegocio){
                    if(documentoesperado.copiasimples && !copiasimples.includes('Atendimento Comercial')){
                        copiasimples.push('Atendimento Comercial');
                    }
                    if(documentoesperado.copiaautenticada && !copiaautenticada.includes('Atendimento Comercial')){
                        copiaautenticada.push('Atendimento Comercial')
                    }
                    if(documentoesperado.original && !original.includes('Atendimento Comercial')){
                        original.push('Atendimento Comercial');
                    }
                }
            }
        }
        return {
            copiasimples: {exige: copiasimples.length > 0 ? true : false, requisitantes: copiasimples.sort().join('/')},
            copiaautenticada: {exige: copiaautenticada.length > 0 ? true : false, requisitantes: copiaautenticada.sort().join('/')},
            original: {exige: original.length > 0 ? true : false, requisitantes: original.sort().join('/')},
            enviarporemail: enviarporemail
        };
    }
    
    salvarDocumento() {
        // Se já enviou o arquivo pro S3, só chamo requisição para salvar negócio documento
        if (this.fileAdded && this.fileUrl != "") {
            this.setBusy('Enviando documento...');
            this.montaEstruturaESalvaAtcDocumento(this.fileUrl);
        }
        else {
            this.setBusy('Enviando documento...');
            this.$scope.$broadcast('processQueue');
        }
    }
    
    removerArquivo() {
        if (this.fileAdded) {
            this.$scope.$broadcast('removeFile');
        }
    }
    
    salvarAtcsDocumentos(documento: any, origem: any) {
    
        let action = '';
    
        if (origem === 'create') {
          this.crmAtcsdocumentos.save(documento, true);
          return;
        } else if (origem === 'recebimento') {
            action = 'crm_atcsdocumentos_recebe_documento';
        } else if (origem === 'pre-analise') {
            action = 'crm_atcsdocumentos_pre_analise';
        }
    
        this.$http({
            method: 'post',
            url: this.nsjRouting.generate(action, { 'id': documento.negociodocumento }, true),
            data: angular.copy(documento),
        }).then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Documento foi enviado com sucesso!'
            });
    
            this.removerArquivo();
            this.carregarDocumentos();
        });
    }
    
    private apagarDocumento(id: String, index: any) {
        let url = this.nsjRouting.generate(
            'crm_atcsdocumentos_delete',
            angular.extend(this.constructors, { id: id }),
            true
        );
        let config = null; // { timeout: this.loading_deferred.promise };
        this.$http.delete(url, config).then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Documento foi excluído com sucesso!'
            });
            this.carregarDocumentos();
        }).catch((response: any) => {
            this.toaster.pop({
                type: 'error',
                title: "Ocorreu um erro ao excluir o Documento!"
            });
        });
    }
    
    private excluirTipoDocumentoRequisitanteAtc(tipodocumento){
        let paraExcluir = [];
        this.entity.negociostiposdocumentosrequisitantes.map((tipodocumentorequisitante) => {
            if(tipodocumentorequisitante.tipodocumento.tipodocumento === tipodocumento.tipodocumento){
                paraExcluir.push(tipodocumentorequisitante);
            }
        });
        let somenteRequisitantesExternos = true;
        for(let i = 0; i < paraExcluir.length; i++){
            if(paraExcluir[i].requisitantenegocio){

                // Se tiver houver atc documento para esse tipo de documento, excluo. Do contrário não preciso chamar função para apagar documento
                if(tipodocumento.documento.hasOwnProperty('negociodocumento')){
                    this.apagarDocumento(tipodocumento.documento.negociodocumento, i);
                }

                somenteRequisitantesExternos = false;
                this.CrmAtcstiposdocumentosrequisitantes.constructors = {'negocio':this.entity.negocio};
                this.CrmAtcstiposdocumentosrequisitantes.delete(paraExcluir[i], false);
            }
        }
        if(somenteRequisitantesExternos){
            this.toaster.pop({
                type: 'error',
                title: 'Não é possível excluir um tipo de documento de um requisitante externo.',
                body: 'Requisitante(s): ' + this.getRequisitante(tipodocumento)
            });
        }
    }

    forceDropzoneClick(){
        if (this.fileAdded) {
            this.removerArquivo();
        }
        this.$scope.$broadcast('forceDropzoneClick');
    }
    
    addFile(file: any) {
        this.fileAdded = true;
        this.fileUrl = "";
        this.habilitarDesabilitarEnvio();
    }
    
    removeFile(file: any) {
        this.documentoParaAlterar = null;
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
    
    successFile(response: any) {
        this.fileUrl = response.s3key;
        this.typeMime = response.file.type;
        this.montaEstruturaESalvaAtcDocumento(response.s3key);
    }

    /**
     * Envia requisição para salvar negócio documento
     * @param url 
     */
    private montaEstruturaESalvaAtcDocumento(url: string){
        let origem = 'create';
    
        if (this.documentoParaAlterar) {
          origem = 'recebimento';
    
          this.documentoParaAlterar.status = 1;
          this.documentoParaAlterar.url = url;
          this.documentoParaAlterar.tipodocumento = this.tipoDocumentoSelecionado;
          this.documentoParaAlterar.tipomime = this.typeMime;
        }
    
        let entity = (this.documentoParaAlterar) ? this.documentoParaAlterar : {
            negocio: (<any>this).entity,
            tipodocumento: this.tipoDocumentoSelecionado,
            requisitante: 1,
            url: url,
            status: 1,
            tipomime: this.typeMime 
        };

        this.mensagemExibida = false;
    
        this.salvarAtcsDocumentos(entity, origem);
    }
    
    itemClicked(documento: any) {
        if (documento.status === 0) {
          this.tipoDocumentoSelecionado = documento.tipodocumento;
          this.documentoParaAlterar = documento;
    
          this.habilitarDesabilitarEnvio();
          this.forceDropzoneClick();
    
          return;
        }
    
        this.showDocumentModal(documento);
    }
    
    showDocumentModal(documento: any) {
        let self = this;
    
        let uibModalInst = this.$uibModal.open({
            scope: this.$scope,
            animation: true,
            keyboard: false,
            backdrop: true,
            size: 'lg',
            template: require('../../Crm/Atcs/documentos.modal.html'),
            controller: function () {
                this.documento = angular.copy(documento);
        
                this.documento.analiseFinalizada = (this.documento.status === 2 || this.documento.status === 5);
        
                this.getRequisitante = () => {
                    return self.getRequisitante(documento.tipodocumento);
                };
        
                this.copiasexigidas = self.getCopiasExigidas(documento.tipodocumento);
                this.finalizarAnalise = function (documentoFinalizado: any) {
                    self.salvarAtcsDocumentos(documentoFinalizado, 'pre-analise');
                    uibModalInst.close(false);
                };
        
                this.close = function () {
                    uibModalInst.close(false);
                };
            },
            controllerAs: 'ctrlModal'
        });
    
        uibModalInst.result.then(
            function closed(item: any) {
                // não implementado
            },
            function dismissed() {
                // não implementado
            }
        );
    }
    
    habilitarDesabilitarEnvio() {
        this.envioHabilitado = (this.fileAdded && this.tipoDocumentoSelecionado != null);

        try {
            this.$scope.$apply();
        } catch (error) {}
    }

    isEnvioHabilitado(): boolean {
        return this.envioHabilitado;
    }

    imagetoPrint(source: any) {
        return '<html><head><script>function step1(){' +
            'setTimeout(step2(), 10);}' +
            'function step2(){window.print();window.close()}' +
            '</script></head><body onload="step1()">' +
            '<img style="width: 100%; " src="' + source + '"/></body></html>';
    }
    
    printImage(documento: any) {
        var Pagelink = 'about:blank';
        var pwa = window.open(Pagelink, '_new');
        pwa.document.open();
        pwa.document.title = documento.negocio.nome;
        pwa.document.write(this.imagetoPrint(documento.url));
        pwa.document.close();
    }

    isBusyDocumentos() {
        return this.busyDocumentos;
    }

    baixarDocumento(id: string, nome: string = 'documento') {
        this.setBusy('Baixando documento...');
        this.crmAtcsdocumentos.baixarDocumento(id).then((response) => {
            // Dados para geração do link
            let link: ILink = {
                nome: nome,
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
}
