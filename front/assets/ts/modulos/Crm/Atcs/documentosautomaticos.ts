

import angular = require('angular');
import { Tree } from '../../Commons/tree';
import { ITreeControl, treeExpand } from '../../Commons/utils';
import { AtcsDocumentosTreeService, ILinhaTreeItemDocumento, EnumTipoDocumentoRetornado } from '../Atcsconfiguracoesdocumentos/atcsDocumentosTreeService';
import { ILink, CommonsUtilsService, EnumTipoLink } from '../../Commons/utils/utils.service';
import { ISecaoController } from './classes/isecaocontroller';

export class CrmAtcsDocumentosAutomaticosController {

    /**
     * Injeção de dependencias da tela.
     */
    static $inject = [
        '$scope',
        'toaster',
        'entity',
        'CrmAtcs',
        'Tree',
        'AtcsDocumentosTreeService',
        'CommonsUtilsService',
        'secaoctrl'
    ];

    /**
     * Define se a tela foi inicializada.
     */
    public inicializado: boolean = false;
    /**
     * Define se a tela está em processamento
     */
    public busy: boolean = false;
    /**
     * Define se a tela está em processamento
     */
    public busyGeracaoDocumento: boolean = false;
    /**
     * Configuração da coluna de expansão da Tree.
     */
    public expanding_property: treeExpand;
    /**
     * Configuração das colunas da tree.
     */
    public col_defs: any;
    /**
     * Objeto da tree de documentos automáticos
     */
    private treeDocsAutomaticos: ITreeControl = {};
    /**
     * Lista de dados da tree em linha
     */
    private arrDadosTreeEmLinha: any[] = [];
    /**
     * Lista de dados da tree em cascata
     */
    private dadosTreeEmCascata: any[] = [];
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;
    private busyInicializacao: boolean = false;

    /**
     * Faz o controle se o accordion de documentos automáticos já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    constructor(
        public $scope: any,
        public toaster: any,
        public entity: any,
        public entityService: any,
        public tree: Tree,
        private AtcsDocumentosTreeService: AtcsDocumentosTreeService,
        private CommonsUtilsService: CommonsUtilsService,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController
    ) {
        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
            }
            
            //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar dados do accordion
            if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
                // Ativo busy de inicialização
                this.busy = true;
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                this.busyInicializacao = true;
                // Chamo função que carrega dados da tela;
                this.carregarDadosTela();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }
    }

    /* Carregamento */
    Init() {
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;
    
            // Configuro a tree
            this.configurarTree();
        }
    }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela() {

        this.busy = true;

        try {
            const dadosTree = await this.AtcsDocumentosTreeService.getListaArvore(this.entity);
            this.arrDadosTreeEmLinha = dadosTree.arrayTree;
        } catch (error) {
        }

        // Desativo loading
        this.busy = false;
        this.busyInicializacao = false;

        //Uma vez que o accordion foi carregado, seto a variável de controle para true
        this.accordionCarregado = true;

        this.reloadScope();
    }

    /**
     * Verifica se a tela está em processamento
     */
    isBusyDocumentos() {
        return this.busy || this.busyGeracaoDocumento;
    }


    /* tree */

    /**
     * Configura a tree de documentos
     */
    configurarTree() {
        this.expanding_property = {
            sortable: true,
            filterable: true,
            field: 'nome',
            displayName: '',
            cellTemplate: `
            <div class="add-tree-table-form container-flex">

                <!-- Se não for um item de documento, só apresento a descrição -->
                <div ng-if="row.branch.tipo != 'itemdocumento'" 
                    class="doc-container" 
                    ng-class="{'atcconfiguracaodocumentoitem': row.branch.tipo == 'atcconfiguracaodocumentoitem'}">

                    <span ng-bind="row.branch.nome"></span>
                </div>

                <!-- Se for um item de documento, apresento um link para gerar e baixar o documento -->
                <div ng-if="row.branch.tipo == 'itemdocumento'" 
                    class="doc-link-container">
                    <span>
                        <a class="link" ng-click="cellTemplateScope.baixarDocumento(this, row)">{{row.branch.nome}}</a>
                    </span>
                </div>
            </div>`,
            cellTemplateScope: {
                baixarDocumento: (_this, row) => {
                    this.baixarDocumento(row.branch.obj);
                },
            }
        }

        /* carrega as colunas */
        this.col_defs = [];
    }

    /**
     * Atualiza a visualização da tree
     */
    reloadScope() {
        this.dadosTreeEmCascata = this.tree.getArvore(this.arrDadosTreeEmLinha);
        this.$scope.$applyAsync();
    }

    /**
     * Dispara rota para gerar e baixar documento
     */
    private baixarDocumento(linhaDocumentoItem: ILinhaTreeItemDocumento) {
        this.busyGeracaoDocumento = true;
        
        // Monto entidade utilizada pelo formulário de baixar documento. Uma entidade ATC, porém só com alguns dados presentes no formulário.
        let entity = {
            negocio: this.entity.negocio,
            fornecedorobj: null,
            documentofop: linhaDocumentoItem.documentofop,
            contratoGeracaoRps: linhaDocumentoItem.contrato !== undefined ? linhaDocumentoItem.contrato.contrato : null,
            tiporelatorioemissao: linhaDocumentoItem.atcsconfiguracoesdocumentositens?.tipo
        }

        if (linhaDocumentoItem.fornecedorEnvolvido != null ) {
            entity.fornecedorobj = linhaDocumentoItem.fornecedorEnvolvido.fornecedor;
        }

        this.entityService.baixardocumento(entity).then((response) => {
            // Dados para geração do link
            let link: ILink = {
                nome: linhaDocumentoItem.nome,
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
                    title: 'Erro ao gerar pdf.'
                });
            }
        }).finally(() => {
            this.busyGeracaoDocumento = false;
        });
    }
}

