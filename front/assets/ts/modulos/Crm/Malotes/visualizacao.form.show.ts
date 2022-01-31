import angular = require('angular');
import { CrmAtcsdocumentos } from './../../Crm/Atcsdocumentos/factory';
import * as moment from 'moment';

export class CrmMalotesVisualizacaoShowController {

    static $inject = [
        '$scope',
        'toaster', 
        'CrmMalotes', 
        'utilService', 
        'nsjRouting', 
        '$http', 
        '$q', 
        'CrmAtcsdocumentos', 
        '$uibModal'
    ];

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

    public mostrarAcoesEmMassa: boolean = false;

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
        this.documentosParaListar = this.entity.documentos.map((docMap) => {
            return docMap.documento;
        });
    }

    getDocumentStyle(documento: any) {
        return this.documentStyle.find(o => o.status === documento.status);
    }

    getRequisitante(documento: any) {
        return this.tiposRequisitantes.find(o => o.cod === documento.requisitante);
    }

    carregarDocumentos() {
      this.documentos = [];
      this.crmAtcsdocumentos.constructors = [];
      this.crmAtcsdocumentos.constructors.id = (<any>this).entity.negocio;
  
      this.$http.get(this.nsjRouting.generate('crm_atcsdocumentos_index', { 'negocio': (<any>this).entity.negocio }))
        .then((response) => {
          this.documentosParaListar = response.data as any;
        });
    }

    montaListaDocumentos(documento: any) {
        return {
            entity: {
                nome: documento.tipodocumento_nome ? documento.tipodocumento_nome : '',
                atendimentonome: documento.negocio_nome, //Verificar onde essa propriedade ainda está como negocio_nome ao invés de atc_nome
                recebido: documento.created_at ? moment(documento.created_at).format('DD/MM/YYYY') : '-',
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

    private apagarDocumento(id: String, index: any) {
        let url = this.nsjRouting.generate('crm_malotesdocumentos_delete',
          angular.extend(this.constructors, { id: id }),
          true);
        let config = null; // { timeout: this.loading_deferred.promise };
        this.$http
          .delete(url, config)
          .then((response: any) => {
            this.toaster.pop({
              type: 'success',
              title: 'O Documento foi excluído com sucesso!'
            });
            this.carregarDocumentos();
          })
          .catch((response: any) => {
            this.toaster.pop({
              type: 'error',
              title: "Ocorreu um erro ao excluir o Documento!"
            });
          });
    
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