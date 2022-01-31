import angular = require('angular');
import { CrmFornecedoresenvolvidos } from '../../Fornecedoresenvolvidos/factory';
import { IFornecedorenvolvido } from '../../Fornecedoresenvolvidos/classes/ifornecedorenvolvido';
import { CrmAtcs } from '../factory';

/**
 * Serviço que vai chamar a modal de solicitar orçamento por e-mail
 */
export class SolicitarOrcamentoEmailModalService {
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
    
    open(parameters: any = {}, subentity: any) {
        return this.$uibModal.open({
            template: require('./modal-solicitar-orcamento-email.html'),
            controller: 'SolicitarOrcamentoEmailModalController',
            controllerAs: 'crm_atcs_slc_orc_email_mdl_ctrl',
            windowClass: '',
            size: 'lg',
            resolve: {
                entity: subentity,
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}

/**
 * Controller da tela modal de enviar documentos do atendimento por e-mail
 */
export class SolicitarOrcamentoEmailModalController {
    static $inject = [
        '$uibModalInstance',
        'entity',
        'constructors',
        'nsjRouting',
        '$scope',
        'toaster',
        '$http',
        'moment',
        'CrmAtcs',
        'CrmFornecedoresenvolvidos'
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
     * Lista de e-mails
     */
    private emails: any[] = [];

    /**
     * Id do prestador selecionado
     */
    public fornecedorid: string;
    /**
     * Lista de fornecedores envolvidos do atendimento
     */
    public arrFornecedoresEnvolvidos: IFornecedorenvolvido[] = [];


    constructor (
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        public nsjRouting: any,
        public $scope: any,
        public toaster: any,
        public $http: any,
        public moment: any,
        public entityService: CrmAtcs,
        public CrmFornecedoresenvolvidos: CrmFornecedoresenvolvidos
    ){
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
     * Evento chamado quando um prestador for selecionado
     */
    async onPrestadorChange(){
        if (this.fornecedorid == '' || this.fornecedorid == null) {
            return;
        }

        // Ativo loading
        this.busy = true;

        try {
            // Limpo lista de e-mails
            this.emails = [];

            // Busco e-mails
            const arrContatos = await this.buscarEmails('');

            arrContatos.forEach((contato) => {
                if (contato.email != null && contato.email.trim() != ''){
                    this.emails.push({
                        email: contato.email
                    });
                }
            });
        } catch (error) {}

        this.busy = false;
        this.reloadScope();
    }

    /**
     * Faz o carregamento e conversão dos dados necessários ao funcionamento da tela
     */
    private async carregarDadosTela(){
        // Busco fornecedores envolvidos
        this.arrFornecedoresEnvolvidos = await this.CrmFornecedoresenvolvidos.getAll(this.entity.negocio);

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
        // Valido se algum fornecedor foi selecionado
        if (this.fornecedorid == null) {
            this.toaster.pop({
                type: 'error',
                title: 'Nenhum prestador foi selecionado!'
            });
            return;
        }

        // Monto entidade utilizada pelo formulário de enviar documentos por e-mail
        let entity = {
            fornecedor: {
                fornecedor: this.fornecedorid
            },
            emails: []
        }

        // Adiciono E-mails
        this.emails.forEach((emailForeach) => {
            entity.emails.push(emailForeach.email);
        });

        // Valido se algum e-mail foi informado
        if (entity.emails.length == 0) {
            this.toaster.pop({
                type: 'error',
                title: 'Nenhum e-mail foi informado!'
            });
            return;
        }

        // Defino que a tela está ocupada enviando e-mails
        this.busyEnvio = true;
        
        // Chamo requisição para enviar documentos do atendimento por e-mail
        this.entityService.solicitarOrcamento(entity, {id: this.entity.negocio}).then((response) => {
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
                    title: 'Erro ao tentar enviar e-mail.'
                });
            }
            this.reloadScope();
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

                // Defino filtro por tipo e pessoa
                filtros.pessoa = this.fornecedorid;
                filtros.tipo = [EnumAtcContatoTipo.actPrestadora];
            }

            // Realizo a busca
            this.getContatosFromApi(busca, filtros).then((response) => {
                response.forEach((contato) => {
                    contato.tipocontatodescricao = 'Prestadora';
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
 * Enum referente aos tipos de envio de e-mail da tela
 */
export enum EnumTipoEnvioEmail {
    teeSeguradora,
    teeCliente
}
