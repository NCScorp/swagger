import angular = require('angular');
import { AtcsDocumentosTreeService, IConfigGetListaArvore, EnumTipoDocumentoRetornado, ILinhaTreeItemDocumento } from '../Atcsconfiguracoesdocumentos/atcsDocumentosTreeService';
import { ITreeControl, treeExpand } from '../../Commons/utils';
import { IAtcConfiguracaoDocumento, IAtcConfiguracaoTemplateEmail, IAtcConfiguracaoDocumentoItem } from '../Atcsconfiguracoesdocumentos/classes';
import { Tree } from '../../Commons/tree';
import { ILink, EnumTipoLink, CommonsUtilsService } from '../../Commons/utils/utils.service';
import { CrmFornecedoresenvolvidos } from '../Fornecedoresenvolvidos/factory';

/**
 * Serviço que vai chamar a modal de enviar atendimento por e-mail
 */
export class EnviarAtcEmailModalService {
    static $inject = [
        '$uibModal', 
        '$http', 
        'nsjRouting', 
        'toaster',
    ];
    
    constructor(
        public $uibModal: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any,
    ) {}
    
    open(parameters: IParamsTela, subentity: any) {
        return this.$uibModal.open({
            template: require('./../../Crm/Atcs/modal-enviaratcemail.html'),
            controller: 'EnviarAtcEmailModalController',
            controllerAs: 'crm_atcs_email_mdl_ctrl',
            windowClass: '',
            size: 'lg',
            resolve: {
                entity: subentity,
                constructors: () => {
                    return parameters;
                }
            },
            backdrop: 'static',
            keyboard: false
        });
    }
}

/**
 * Controller da tela modal de enviar atendimento por e-mail
 */
export class EnviarAtcEmailModalController {
    static $inject = [
        '$uibModalInstance',
        'entity',
        'constructors',
        'nsjRouting',
        '$scope',
        'toaster',
        '$http',
        'moment',
        'Tree',
        'AtcsDocumentosTreeService',
        'CrmAtcs',
        'CommonsUtilsService',
        'CrmFornecedoresenvolvidos',
    ];

    /**
     * Define se a tela está em processamento
     */
    private busy: boolean = false;
    /**
     * Define se a tela está em enviando e-mails
     */
    private busyEnvio: boolean = false;
    /**
     * Define se a tela está processando operações com documentos
     */
    private busyDocumentos: boolean = false;
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
     * Configuração de geração dos documentos
     */
    private configuracaoDocumentos: IAtcConfiguracaoDocumento;
    /**
     * Template de e-mail escolhido para o envio de e-mail
     */
    private templateEmailID: string;
    /**
     * Lista de e-mails
     */
    private emails: any[] = [];
    /**
     * Objeto utilizado pela tree
     */
    private configTree: {
        expandingProperty: treeExpand,
        colunas: any[]
    }

    private identificacaoEnvioEmailNome: string;
    private identificacaoEnvioEmailTipo: number;

    /**
     * Lista de fornecedores envolvidos no atendimento
     */
    private arrFornecedoresEnvolvidos: any[] = [];

    constructor (
        public $uibModalInstance: any,
        public entity: any,
        public constructors: IParamsTela,
        public nsjRouting: any,
        public $scope: any,
        public toaster: any,
        public $http: any,
        public moment: any,
        public tree: Tree,
        public AtcsDocumentosTreeService: AtcsDocumentosTreeService,
        public entityService: any,
        private CommonsUtilsService: CommonsUtilsService,
        private CrmFornecedoresenvolvidos: CrmFornecedoresenvolvidos,
    ){
        this.configTree = {
            expandingProperty: null,
            colunas: []
        }
    }

    async $onInit() {
        // Apresento loading de carregamento
        this.busy = true;
        // Limpo dados
        this.entity.mensagememail = "";
        this.emails = [];
        // Configuro a tree de documentos
        this.configurarTree();
        
        //Recupero os fornecedoresenvolvidos        
        this.arrFornecedoresEnvolvidos = await this.CrmFornecedoresenvolvidos.getAll(this.entity.negocio);
        // Chamo função de carregamento inicial da tela
        this.carregarDadosTela();
    }

    isBusy(): boolean {
        return this.busy;
    }

    /**
     * Faz o carregamento e conversão dos dados necessários ao funcionamento da tela
     */
    private async carregarDadosTela(){        
        // Defino configuração para montar árvore de documentos
        let configuracao: IConfigGetListaArvore = {
            tipoDocumento: EnumTipoDocumentoRetornado.tdrAmbos,
            arrPrestadores: [],
            acoesItem: []
        };
        this.identificacaoEnvioEmailTipo = 0;
        // Se estiver enviando para seguradora, só aparecem documentos referentes a seguradora.
        if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teeSeguradora) {
            configuracao.tipoDocumento = EnumTipoDocumentoRetornado.tdrSeguradora;
            this.identificacaoEnvioEmailNome = 'Seguradora: ' + this.entity.cliente.nomefantasia;
            this.identificacaoEnvioEmailTipo = 1;
        } 
        // Se estiver enviando para prestadora, só aparecem documentos referentes a prestadora que foi passada.
        else if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teePrestadora) {
            configuracao.tipoDocumento = EnumTipoDocumentoRetornado.tdrPrestadora;
            configuracao.arrPrestadores = [this.constructors.pessoa];
            // Itera o array de fornecedores para localizar o fornecedor passado
            let currentFornecedor = this.arrFornecedoresEnvolvidos.filter(f => f.fornecedor.fornecedor === this.constructors.pessoa);
            if(currentFornecedor !== null ) {
                this.identificacaoEnvioEmailNome = 'Prestadora: ' + currentFornecedor[0].fornecedor.nomefantasia;
                this.identificacaoEnvioEmailTipo = 2;
            }
        }

        // Configuro lista de ações dos itens de documentos
        configuracao.acoesItem.push({
            label: 'Visualizar',
            icon: 'fas fa-eye',
            size: 'xs', 
            color: 'primary',
            acao: (node: any) => {
                this.baixarEVisualizar(node.obj);
            },
            isVisible: (node: any): boolean => {
                return true;
            },
        })

        // Defino busca de dados da tree de documentos
        let dadosTree: {
            configuracao: IAtcConfiguracaoDocumento,
            arrayTree: any[]
        };
        let arrResultado: any[] = [dadosTree];
        const arrPromises: Promise<any>[] = [ this.AtcsDocumentosTreeService.getListaArvore(this.entity, configuracao) ];

        // Se estiver enviando para seguradora, defino busca de pre-seleção de e-mails
        if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teeSeguradora) {
            arrResultado.push([]);
            arrPromises.push( 
                this.buscarEmails('', {
                    pessoa: this.constructors.pessoa,
                    tipo: EnumAtcContatoTipo.actSeguradoraProduto
                })
            );
        }  else if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teePrestadora) {
            arrResultado.push([]);
            arrPromises.push(
                this.buscarEmails('', {
                    pessoa: this.constructors.pessoa,
                    tipo: EnumAtcContatoTipo.actPrestadora,
                    principal: 1
                })
            );
        }

        // Busco dados
        arrResultado = await Promise.all(arrPromises);

        // Seto dados referente a arvore de documentos
        dadosTree = arrResultado[0];
        this.configuracaoDocumentos = dadosTree.configuracao;
        this.arrDadosTreeEmLinha = dadosTree.arrayTree;

        // Seleciono contatos dos produtos da seguradora
        if (arrResultado.length > 1) {
            (<IAtcContato[]>arrResultado[1]).forEach((contato) => {
                if (contato.email != null && contato.email.trim() != ''){
                    this.emails.push({
                        email: contato.email
                    });
                }
            });
        }

        if (this.configuracaoDocumentos.atcsconfiguracoestemplatesemails.length > 0) {
            this.templateEmailID = this.configuracaoDocumentos.atcsconfiguracoestemplatesemails[0].atcconfiguracaotemplateemail;
        }

        // Removo loading de carregamento
        this.busy = false;
        this.reloadScope();
    }

    /**
     * Baixa o documento e abre ele em outra aba
     * @param linhaDocumentoItem 
     */
    private baixarEVisualizar(linhaDocumentoItem: ILinhaTreeItemDocumento) {
        this.busyDocumentos = true;
        
        // Monto entidade utilizada pelo formulário de baixar documento. Uma entidade ATC, porém só com alguns dados presentes no formulário.
        let entity = {
            negocio: this.entity.negocio,
            fornecedorobj: null,
            documentofop: linhaDocumentoItem.documentofop,
            contratoGeracaoRps: linhaDocumentoItem.contrato !== undefined ? linhaDocumentoItem.contrato.contrato : null
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

            this.CommonsUtilsService.abrirFromLink(link, EnumTipoLink.tlBinary);
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
            this.busyDocumentos = false;
        });
    }

    isBusyDocumentos(){
        return this.busyDocumentos;
    }

    /**
     * Atualiza a visualização da tree
     */
    reloadScope() {
        this.dadosTreeEmCascata = this.tree.getArvore(this.arrDadosTreeEmLinha);
        this.$scope.$applyAsync();
    }

    /**
     * Configura a tree de documentos
     */
    configurarTree() {
        this.configTree.expandingProperty = {
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

                <!-- Se for um item de documento, apresento checkbox para marcação -->
                <div ng-if="row.branch.tipo == 'itemdocumento'" 
                    class="doc-link-container">
                    <span class="check">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="check" ng-model="row.branch.obj.checked"/>
                            <span class="checkmark"></span>
                        </label>
                    </span>
                    <span class="nome">
                        {{row.branch.nome}}
                    </span>
                </div>
            </div>`,
            cellTemplateScope: {}
        }

        /* carrega as colunas */
        this.configTree.colunas = [];

        // Ações da tabela
        this.configTree.colunas.push({
            field: null,
            cellTemplate: `
                <div class='table-btns-actions'
                    ng-class="row.branch.getActions().length == 0 ? 'naopossuiacoes' : ''" >
                    <nsj-actions>
                       <nsj-action 
                            ng-repeat="action in row.branch.actions" 
                            ng-click='action.acao(row.branch)'
                            ng-if='action.isVisible(row.branch)' 
                            icon='{{action.icon}}'
                            title='{{action.label}}'
                        >
                        </nsj-action>
                    </nsj-actions>
                </div>
            `,
            cellTemplateScope: {}
        });
    }

    /**
     * Verifica se algum documento já foi selecionado na tela
     */
    private possuiDocumentosSelecionados(): boolean {
        return this.arrDadosTreeEmLinha.some((atendimentoSome) => {
            return atendimentoSome.obj.checked;
        });
    }

    /**
     * Envia e-mail com os dados do atendimento
     */
    private enviarEmail(){
        // Monto entidade utilizada pelo formulário de baixar documento.
        let entity = {
            negocio: this.entity.negocio,
            mensagememail: this.entity.mensagememail,
            documentosemail: [],
            listaemails: [],
            atcconfiguracaodocumento: this.configuracaoDocumentos.atcconfiguracaodocumento,
            atcconfiguracaotemplateemail: {
                atcconfiguracaotemplateemail: this.templateEmailID
            },
            identificacaoEnvioEmailNome: this.identificacaoEnvioEmailNome,
            identificacaoEnvioEmailTipo: this.identificacaoEnvioEmailTipo
        };

        // Adiciono E-mails
        this.emails.forEach((emailForeach) => {
            entity.listaemails.push(emailForeach.email);
        });

        // Valido se algum e-mail foi informado
        if (entity.listaemails.length == 0) {
            this.toaster.pop({
                type: 'error',
                title: 'Nenhum e-mail foi informado!'
            });
            return;
        }

        // Adiciono documentos a entidade da requisição
        this.arrDadosTreeEmLinha.filter((dadoFilter) => {
            return dadoFilter.tipo == 'itemdocumento' && dadoFilter.obj.checked;
        }).forEach((dadoForeach) => {
            let documento = {
                fornecedorobj: dadoForeach.obj.fornecedorEnvolvido?.fornecedor,
                documentofop: dadoForeach.obj.documentofop,
                contratoGeracaoRps: dadoForeach.obj.contrato !== undefined ? dadoForeach.obj.contrato.contrato : null,
                tiporelatorioemissao: dadoForeach.obj.atcsconfiguracoesdocumentositens.tipo
            }
            entity.documentosemail.push(documento);
        });

        // Valido se algum documento foi selecionado
        if (entity.documentosemail.length == 0) {
            this.toaster.pop({
                type: 'error',
                title: 'Nenhum documento foi selecionado!'
            });
            return;
        }
        
        // Valido se algum template de e-mail foi selecionado
        if (!this.templateEmailID) {
            this.toaster.pop({
                type: 'error',
                title: 'Nenhum template de e-mail foi escolhido!'
            });
            return;
        }

        // Defino que a tela está ocupada enviando e-mails
        this.busyEnvio = true;
        
        // Chamo requisição para enviar documentos do atendimento por e-mail
        this.entityService.enviaratendimentoemail(entity).then((response) => {
            // Removo loading de envio de e-mail
            this.busyEnvio = false;

            // Apresento toaster de sucesso e fecho tela
            this.toaster.pop({
                type: 'success',
                title: 'E-mail enviado com sucesso!'
            });
            this.fecharTela({tipo: "sucesso"});
        }).catch((response) => {
            // Removo loading de envio de e-mail
            this.busyEnvio = false;

            // Apresento toaster da mensagem de erro
            if (response != null && (response.data) != null && response.data.message) {
                this.toaster.pop({
                    type: 'error',
                    title: response.data.message
                });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao tentar enviar documentos por e-mail.'
                });
            }
        });
    }

    /**
     * Retorna a lista de contatos relacionados ao atendimento:
     *  - Contatos do cliente
     *  - Contatos de fornecedores envolvidos
     * @param busca 
     */
    private buscarEmails(busca: string, filtros: any = null): Promise<IAtcContato[]>{
        return new Promise((resolve, reject) => {
            if (filtros == null) {
                filtros = {};

                // Defino filtro por tipo e pessoa, dependendo do tipo de envio de e-mail da tela
                if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teeSeguradora) {
                    filtros.pessoa = this.constructors.pessoa;
                    filtros.tipo = [
                        EnumAtcContatoTipo.actSeguradoraProduto
                    ];
                } else if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teePrestadora) {
                    filtros.pessoa = this.constructors.pessoa;
                    filtros.tipo = EnumAtcContatoTipo.actPrestadora;
                }
            }

            // Realizo a busca
            this.getContatosFromApi(busca, filtros).then((response) => {
                let arrContatos: IAtcContato[] = [];
                let arrContatosIDs: string[] = [];
                response.forEach((contato) => {
                    contato.tipocontatodescricao = this.getTipoContatoDescricao(contato.tipo);

                    if (arrContatosIDs.indexOf(contato.contato) == -1){
                        arrContatosIDs.push(contato.contato);
                        arrContatos.push(contato);
                    }
                });
                
                if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teeTodos) {
                    resolve(arrContatos);
                } else {
                    resolve(response);
                }
            }).catch((err) => {
                reject(err);
            })
        });
    }

    /**
     * Busca lista de contatos da api
     * @param busca 
     * @param filtros 
     */
    private getContatosFromApi(busca: string, filtros: any = {}): Promise<IAtcContato[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_atcclientefornecedorescontatos_index', {negocio: this.entity.negocio, filter: busca, ...filtros}, true)
            }).then((dados: any) => {
                resolve(dados.data);
            }).catch((error) => {
                reject(error);
            });    
        });
    }

    /**
     * Retorna a descrição do Tipo de contato
     * @param tipo 
     */
    private getTipoContatoDescricao(tipo: EnumAtcContatoTipo): string {
        if (tipo == EnumAtcContatoTipo.actCliente) {
            if (this.entity.possuiseguradora) {
                return 'Seguradora';
            } else {
                return 'Cliente';
            }
        } else if (tipo == EnumAtcContatoTipo.actPrestadora) {
            return 'Prestadora';
        } else if (tipo == EnumAtcContatoTipo.actSeguradoraProduto) {
            return 'Seguradora'
        }
    }

    fecharTela(resposta: any){
        this.$uibModalInstance.close(resposta);
    }

    close() {
        if (!this.isBusy()) {
            this.$uibModalInstance.dismiss('fechar');
        }
    }
}

// Interfaces utilizadas pela tela
interface IAtcContato {
    contato?: string;
    pessoa?: string;
    negocio?: string;
    nome?: string;
    primeironome?: string;
    segundonome?: string;
    nomecompleto?: string;
    cargo?: string;
    setor?: string;
    email?: string;
    pessoanomefantasia?: string;
    tipo?: EnumAtcContatoTipo;
    /**
     * Não mapeado, para uso local
     */
    tipocontatodescricao?: string;
}

enum EnumAtcContatoTipo {
    actCliente = 1,
    actPrestadora = 2,
    actSeguradoraProduto = 3
}

/**
 * Interface referente aos parâmetros enviados a tela
 */
export interface IParamsTela {
    /**
     * Define os filtros de contatos e tipos de documentos que serão gerados e enviados por e-mail.
     */
    tipoEnvioEmail: EnumTipoEnvioEmail;
    /**
     * Guid referente entidade que está enviando e-amail.
     * Pode ser o guid da prestadora ou seguradora.
     */
    pessoa?: string;
}

/**
 * Enum referente aos tipos de envio de e-mail da tela
 */
export enum EnumTipoEnvioEmail {
    teePrestadora,
    teeSeguradora,
    teeTodos
}


