import angular = require('angular');

/**
 * Controller da modal de escolher prestadores do pedido.(Propostaitem)
 */
export class CrmCidadesInfoFunerariasPretadoresModalController {
    static $inject = [
        '$uibModalInstance',
        'constructors',
        'NsFornecedores',
    ];

    // Declaro o enums no controller para utilizar no html
    private EnumPrestadoraStatus = EnumPrestadoraStatus;

    /**
     * Lista de entidades da tela
     */
    private entities: IFornecedor[] = [];

    constructor(
        public $uibModalInstance: any,
        public constructors: any,
        public service: any,
    ) {
        this.service.filters = {};

        this.entities = this.service.reload();
    }

    /**
     * Retorna se o service está buscando alguma informação
     */
    private isBusy(): boolean {
        return this.service.loadParams.busy;
    }

    /**
     * Fecha a modal, sem retornar informações
     */
    private close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Faz a busca dos dados, passando filtros
     * @param filter 
     */
    search(filter: any) {
        let entities = this.service.search(filter);

        return entities;
    }

    /**
     * Disparado quando uma linha da lista for clicada
     */
    onPrestadoraClick(prestadora: IFornecedor) {
        prestadora.checked = !prestadora.checked;
    }

    /**
     * Retorna a lista de fornecedores selecionados para adicão
     */
    salvar(){
        let arrFornecedoresSelecionados: IFornecedor[] = this.entities.filter((entityFilter) => {
            return entityFilter.checked;
        })
        
        this.$uibModalInstance.close({
            fornecedores: arrFornecedoresSelecionados
        });
    }

    /**
     * Retorna o nome do status da prestadora
     */
    private getStatusNome(status: EnumPrestadoraStatus): string {
        let descricao = '';
        
        if (status == EnumPrestadoraStatus.psAtiva) {
            descricao = 'ATIVA';
        } else if (status == EnumPrestadoraStatus.psBanida) {
            descricao = 'BANIDA';
        } else if (status == EnumPrestadoraStatus.psSuspensa) {
            descricao = 'SUSPENSA';
        }

        return descricao;
    }

    /**
     * Verifica se todos os itens da lista estão selecionados
     */
    private estaoTodosSelecionados(): boolean {
        return !this.entities.some((entitySome) => {
            return !entitySome.checked;
        });
    }

    /**
     * Se não estiverem todos selecionados, seleciona todos.
     * Se todos estiverem selecionados, desmarca todos
     */
    private selectAll() {
        const todosSelecionados = this.entities.every((entitySome) => {
            return entitySome.checked;
        });

        let selecionar = true;
        
        if (todosSelecionados){
            selecionar = false;
        }

        this.entities.forEach((entity) => {
            entity.checked = selecionar;
        })
    }
}

/**
 * Service da modal de escolher prestadores do pedido.(Propostaitem)
 */
export class CrmCidadesInfoFunerariasPretadoresModalService {
    static $inject = [
        '$uibModal'
    ];
 
    constructor(
        public $uibModal: any
    ) {}
    
 
    open(parameters: any) {
        return  this.$uibModal.open({
            template: require('./../../Ns/Cidadesinformacoesfunerarias/modal-prestadores.html'),
            controller: 'CrmCidadesInfoFunerariasPretadoresModalController',
            controllerAs: 'crm_cidfu_fncd_mdl_ctrl',
            windowClass: '',
            size: 'lg',
            resolve: {
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}

// Interfaces utilizadas na tela
interface IFornecedor {
    fornecedor?: string;
    razaosocial?: string;
    nomefantasia?: string;
    cnpj?: string;
    status?: EnumPrestadoraStatus;
    checked?: boolean
}

enum EnumPrestadoraStatus {
    psAtiva = "0",
    psSuspensa = "1",
    psBanida = "2"
}
