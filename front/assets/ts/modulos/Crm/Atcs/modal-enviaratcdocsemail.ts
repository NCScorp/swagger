import angular = require('angular');
import { AtcsDocumentosTreeService, IConfigGetListaArvore, EnumTipoDocumentoRetornado, ILinhaTreeItemDocumento } from '../Atcsconfiguracoesdocumentos/atcsDocumentosTreeService';
import { ITreeControl, treeExpand } from '../../Commons/utils';
import { IAtcConfiguracaoDocumento, IAtcConfiguracaoTemplateEmail, IAtcConfiguracaoDocumentoItem } from '../Atcsconfiguracoesdocumentos/classes';
import { Tree } from '../../Commons/tree';
import { ILink, EnumTipoLink, CommonsUtilsService } from '../../Commons/utils/utils.service';

/**
 * Serviço que vai chamar a modal de enviar documentos do atendimento por e-mail
 */
export class EnviarAtcDocsEmailModalService {
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
            template: require('./../../Crm/Atcs/modal-enviaratcdocsemail.html'),
            controller: 'EnviarAtcDocsEmailModalController',
            controllerAs: 'crm_atcs_docs_email_mdl_ctrl',
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
 * Controller da tela modal de enviar documentos do atendimento por e-mail
 */
export class EnviarAtcDocsEmailModalController {
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
        'CommonsUtilsService'
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
        private CommonsUtilsService: CommonsUtilsService
    ){
        this.configTree = {
            expandingProperty: null,
            colunas: []
        }
    }

    $onInit() {
        // Apresento loading de carregamento
        this.busy = true;
        // Limpo dados
        this.entity.mensagememail = "";
        this.emails = [];
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
        // Monto filtro de busca de e-mails
        const filtros = {
            pessoa: this.constructors.pessoa,
            tipo: EnumAtcContatoTipo.actCliente
        }

        let prefixTipo = 'Cliente: ';
        this.identificacaoEnvioEmailTipo = 0;

        if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teeSeguradora) {
            filtros.tipo = EnumAtcContatoTipo.actSeguradoraProduto;
            prefixTipo = 'Seguradora: ';
            this.identificacaoEnvioEmailTipo = 1;
        }
        
        this.identificacaoEnvioEmailNome = prefixTipo + this.entity.cliente.nomefantasia;

        // Busco e-mails do cliente/seguradora
        try {
            const arrContatos = await this.buscarEmails('', filtros);

            arrContatos.forEach((contato) => {
                if (contato.email != null && contato.email.trim() != ''){
                    this.emails.push({
                        email: contato.email
                    });
                }
            });
        } catch (error) {}

        // Removo loading de carregamento
        this.busy = false;
        this.reloadScope();
    }

    /**
     * Atualiza a visualização da tree
     */
    reloadScope() {
        this.$scope.$applyAsync();
    }

    /**
     * Envia e-mail com os dados do atendimento
     */
    private enviarEmail(){
        // Monto entidade utilizada pelo formulário de enviar documentos por e-mail
        let entity = {
            negocio: this.entity.negocio,
            mensagememail: this.entity.mensagememail,
            listaemails: [],
            identificacaoEnvioEmailNome : this.identificacaoEnvioEmailNome,
            identificacaoEnvioEmailTipo: this.identificacaoEnvioEmailTipo
        }

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

        // Defino que a tela está ocupada enviando e-mails
        this.busyEnvio = true;
        
        // Chamo requisição para enviar documentos do atendimento por e-mail
        this.entityService.enviardocumentosporemail(entity).then((response) => {
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
                filtros.pessoa = this.constructors.pessoa;
                filtros.tipo = [];

                if (this.constructors.tipoEnvioEmail == EnumTipoEnvioEmail.teeSeguradora) {
                    filtros.tipo.push(EnumAtcContatoTipo.actSeguradoraProduto);
                } else {
                    filtros.tipo.push(EnumAtcContatoTipo.actCliente);
                }
            }

            // Realizo a busca
            this.getContatosFromApi(busca, filtros).then((response) => {
                response.forEach((contato) => {
                    contato.tipocontatodescricao = this.getTipoContatoDescricao(contato.tipo);
                })
                resolve(response);
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
    teeSeguradora,
    teeCliente
}
