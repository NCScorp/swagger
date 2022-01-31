import angular = require('angular');
import { CrmMalotesDefaultController as ParentController} from './../../Crm/Malotes/default.form';
import { IMaloteApi, EnumMaloteStatus } from './classes/classes';

export class CrmMalotesDefaultController {

    static $inject = [
        '$scope', 'toaster', 'CrmMalotes', 'utilService', 'nsjRouting', '$http', '$q', 'CrmAtcsdocumentos',
        'moment', 'MalotesAdicionarDocumentosModalService', '$uibModal'];

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
            { cod: 2, valor: 'Template da Proposta' },
            { cod: 3, valor: 'Fornecedor' },
        ];
    
        public documentosParaListar: any[] = [];
    
        public configObjectlist = {
            image: null,
            nome: '',
            atendimentonome: 'Atendimento',
            recebido: 'Recebido em',
            label: 'Situação',
            actions: null
        }

        /**
         * Guarda os valores dos filtros de documentos
         */
        private filtersDocumentos: any = {
            documentostatus: -1,
            documentostipocopia: -1 //0-original, 1-autenticada, 2-simples
        };

        public mostrarAcoesEmMassa: boolean = true;

        public entity: any;
        public form: any;
        public constructors: any;
        public collection: any;
        public busy: boolean;
        public action: string;
        public idCount: number = 0;

        constructor( 
            public $scope: angular.IScope, 
            public toaster: any, 
            public entityService: any, 
            public utilService: any,
            public nsjRouting: any, 
            public $http: angular.IHttpService, 
            public $q: angular.IQService,
            public CrmAtcsdocumentos: any,
            public moment: any,
            private MalotesAdicionarDocumentosModalService: any,
            public $uibModal: any
        ) {
            this.montaListaDocumentos = this.montaListaDocumentos.bind(this);
        }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        // Removo o guid do malote documente, para garantir que não chame a função de update do malote documento. 
        // Visto que só existem funções de inserir e excluir

        if (this.entity != null) {
            if (this.entity.malote == null) {
                this.entity.documentos = [];
            }

            (<IMaloteApi>this.entity).documentos = (<IMaloteApi>this.entity).documentos.map((docMap) => {
                docMap.malotedocumento = null;
                docMap.malote = (<IMaloteApi>this.entity).malote;
                return docMap;
            });
            this.carregarDocumentos();
        }

        // Observo mudanças no requisitante cliente, pois o malote só deve possuir documentos de atendimentos relacionados ao requisitante
        this.$scope.$watch('$ctrl.entity.requisitantecliente', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                if(oldValue != null && (<IMaloteApi>this.entity).documentos != null && (<IMaloteApi>this.entity).documentos.length > 0) {
                    const confirmaMudanca = confirm('Se mudar o requisitante, perderá os documentos do malote. Deseja continuar?');

                    if (confirmaMudanca) {
                        (<IMaloteApi>this.entity).documentos = [];
                        this.carregarDocumentos();
                    } else {
                        this.entity.requisitantecliente = oldValue;
                    }
                }
            }
        }, true);
    }

    getDocumentStyle(documento: any) {
        return this.documentStyle.find(o => o.status === documento.status);
    }

    getRequisitante(documento: any) {
        return this.tiposRequisitantes.find(o => o.cod === documento.requisitante);
    }

    verificaTodosDocumentosDesmarcados(listaDocumentos: any[]){

        const todosDesmarcados = listaDocumentos.every(function(documento){
            return documento.checked != true;
        });

        return todosDesmarcados;
    }

    apagaDocumentosEmMassa(listaDocumentos: any[]){
        
        const todosDesmarcados = this.verificaTodosDocumentosDesmarcados(listaDocumentos);
        
        // Se todos os documentos estiverem desmarcados, não faço nada
        if (todosDesmarcados){
            return;
        }
        
        else{

            // Busco os documentos marcados na lista e para cada um deles, chamo a função apagarDocumento
            for(let i = 0; i < listaDocumentos.length; i++){
                if(listaDocumentos[i].checked == true){
                    this.apagarDocumento(listaDocumentos[i], i);
                }
            }
        }
    }

    /**
     * Verifica se todos os itens da lista estão selecionados
     */
    private estaoTodosSelecionados(): boolean {
        return !this.documentosParaListar.some((entitySome) => {
            return !entitySome.checked;
        });
    }

    /**
     * Se não estiverem todos selecionados, seleciona todos.
     * Se todos estiverem selecionados, desmarca todos
     */
    private selectAll() {

        const todosSelecionados = this.documentosParaListar.every((entitySome) => {
            return entitySome.checked;
        });

        let selecionar = true;
        
        if (todosSelecionados){
            selecionar = false;
        }

        this.documentosParaListar.forEach((entity) => {
            entity.checked = selecionar;
        })

        this.documentosParaListar = [...this.documentosParaListar];

    }

    /**
     * Função chamada pelo objectlist de documentos
     * @param lista 
     */
    private atualizarListaDocumentos(lista: any[]){
        this.documentosParaListar = lista;
    }

    /**
     * Carrega a listagem de documentos apresentada na tela.
     */
    carregarDocumentos() {
        this.documentosParaListar = [];
        //filtro documento status
        // let resultadoFiltro = (<IMaloteApi>this.entity).documentos.filter((docFilter) => {
        let resultadoFiltro = (this.entity).documentos.filter((docFilter) => {
            if (this.filtersDocumentos.documentostatus == null || this.filtersDocumentos.documentostatus == -1) {
                return true
            } else {
                return docFilter.documento.status == this.filtersDocumentos.documentostatus;
            }
        });
        
        //filtro tipo copia
        resultadoFiltro = resultadoFiltro.filter((docFilter) => {
            if (this.filtersDocumentos.documentostipocopia == null || this.filtersDocumentos.documentostipocopia == -1) {
                return true
            } else {
                switch (this.filtersDocumentos.documentostipocopia) {
                    case 0:
                        return docFilter.tiposCopias.original
                        break;
                    case 1:
                        return docFilter.tiposCopias.copiaautenticada
                        break;
                    case 2:
                        return docFilter.tiposCopias.copiasimples
                        break;
                    default:
                        break;
                }
            }
        });

        resultadoFiltro.map(doc => {
            this.documentosParaListar.push(doc.documento);
        });
    }

    montaListaDocumentos(documento: any) {
        let retorno = {
            entity: {
                nome: documento.tipodocumento_nome ? documento.tipodocumento_nome : '',
                atendimentonome: documento.negocio_nome, //Verificar onde essa propriedade ainda está como negocio_nome ao invés de atc_nome
                recebido: documento.created_at ? this.moment(documento.created_at).format('DD/MM/YYYY') : '-',
            },
            label: {
                color: this.getDocumentStyle(documento).cor,
                text: this.getDocumentStyle(documento).texto,
            },
            actions: [
                {
                    label: 'Baixar',
                    icon: 'fas fa-download fa-fw',
                    href: documento.url
                },
                {
                    label: 'Visualizar',
                    icon: 'far fa-eye',
                    method: (documento: any, index: any) => {
                        this.showDocumentModal(documento);
                    }
                },
                {
                    label: 'Imprimir',
                    icon: 'fas fa-print fa-fw',
                    method: (documento: any, index: any) => {
                        this.printImage(documento)
                    }
                }
            ],
            image: {
                link: '',
                src: documento.status > 0 ? documento.url : null,
            },
        }

        // Se for edição e malote com status "Novo"
        if ((<IMaloteApi>this.entity).malote == null || (<IMaloteApi>this.entity).status == EnumMaloteStatus.msNovo) {
            retorno.actions.push({
                label: 'Excluir',
                icon: 'far fa-trash-alt',
                method: (malotedocumento: any, index: any) => {
                    this.apagarDocumento(malotedocumento, index);
                }
            });
        }

        return retorno;
    }

    printImage(documento: any) {
        var Pagelink = 'about:blank';
        var pwa = window.open(Pagelink, '_new');
        pwa.document.title = documento.atc_nome;
        pwa.document.open();
        pwa.document.write(this.imagetoPrint(documento.url));
        pwa.document.close();
    }

    imagetoPrint(source: any) {
        return '<html><head><script>function step1(){' +
          'setTimeout(step2(), 10);}' +
          'function step2(){window.print();window.close()}' +
          '</script></head><body onload="step1()">' +
          '<img style="width: 100%; " src="' + source + '"/></body></html>';
    }

    private apagarDocumento(malotedocumento: any, index: any) {
        const docIndex = (<IMaloteApi>this.entity).documentos.findIndex((docFind) => {
            return docFind.documento.negociodocumento == malotedocumento.negociodocumento;
        });
        (<IMaloteApi>this.entity).documentos.splice(docIndex, 1);
        this.carregarDocumentos();
    }

    /**
     * Evento disparado ao clicar no botão "Adicionar Documentos"
     */
    private onBtnAdicionarDocumentosClick(){
        if (this.entity.documentos == null) {
            this.entity.documentos = [];
        }
        // Vou abrir a modal de adicionar documentos
        let modal = this.MalotesAdicionarDocumentosModalService.open({}, this.entity);
        modal.result.then( (retorno: any) => {
            if (retorno.tipo == "sucesso"){
                (<IMaloteApi>this.entity).documentos = retorno.arrMalotesDocumentos;
                this.carregarDocumentos();
            }
        })
        .catch( (error: any) => {});
    }

    /**
     * Abre a modal de visualização do documento
     * @param documento 
     */
    showDocumentModal(documento: any) {
        let self = this;
    
        let uibModalInst = this.$uibModal.open({
            scope: this.$scope,
            animation: true,
            keyboard: false,
            backdrop: true,
            size: 'lg',
            template: require('../../Crm/Malotes/malote-documentos.modal.html'),
            controller: function () {
                this.documento = angular.copy(documento);
                
                // this.getRequisitante = () => {
                //     return self.getRequisitante(documento.tipodocumento_nome);
                // };
        
                // this.copiasexigidas = self.getCopiasExigidas(documento.tipodocumento_nome);
        
                this.close = function () {
                    uibModalInst.close(false);
                };
            },
            controllerAs: 'ctrlModal'
        });
    
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.malote = response.data.malote;
                    resolve(this.entity);
                })
                    .catch((response: any) => {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let message = this.utilService.parseValidationMessage(response.data.errors.children);
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: 'Erro de Validação',
                                        body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                        bodyOutputType: 'trustedHtml'
                                    });
                            } else {
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop(
                                {
                                    type: 'error',
                                    title: 'Erro ao adicionar.'
                                });
                        }
                    });
            });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
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
}