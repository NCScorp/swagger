import angular = require('angular');
import { IListadavezVendedor, IListadavezvendedoritem, IVendedor } from './classes';

/**
 * Controller da modal de listas da vez
 */
export class CrmListaDaVezListasModalController {
    /**
     * Lista de serviços injetados no controller
     */
    static $inject = [
        'entity',
        '$rootScope',
        '$scope',
        'toaster',
        '$uibModalInstance',
        'NsVendedores',
        'CrmListadavezvendedores',
        'utilService'
    ];

    // Declaro enuns para utilizar no HTML
    private EnumSituacaoTela = EnumSituacaoTela;

    /**
     * Define se apresento bloco de adição de vendedores
     */
    private adicionandoVendedores: boolean = false;
    
    /**
     * Define se o negócio está sendo criado, editado ou visualizado
     */
    private situacaoTela: EnumSituacaoTela = EnumSituacaoTela.stNovo;

    /**
     * Lista de vendedores a serem adicionados
     */
    private arrVendedores: IVendedor[] = [];
    
    /**
     * Texto utilizado para buscar vendedores
     */
    private filterVendedores: string = '';

    /**
     * Loading ao salvar lista de vendedores
     */
    private busySave: boolean = false;
    
    constructor(
        private entity: IListadavezVendedor,
        private $rootScope: any,
        private $scope: any,
        private toaster: any,
        private $uibModalInstance: any,
        private serviceVendedores: any,
        private entityService: any,
        private utilService: any
    ){
        if (this.entity != null) {
            this.situacaoTela = EnumSituacaoTela.stVisualizacao;

            // Posiciono itens de acordo com a propriedade posição
            this.entity.itens = [... this.entity.itens.sort((itemA, itemB) => {
                if (itemA.posicao < itemB.posicao) {
                    return -1;
                } else {
                    return 1;
                }
            })]
        } else {
            this.entity = {
                nome: 'Nova lista',
                itens: []
            }
        }
    }

    $onInit(){
        this.$rootScope.$on('ns_vendedores_list_finished', (event: any, args: any) => {
            let entities = args;
            this.arrVendedores = [];

            entities.forEach(vendedorForeach => {
                if (this.entity.itens.findIndex((vendedorFind) => {
                    return vendedorFind.vendedor.vendedor_id == vendedorForeach.vendedor_id;
                }) < 0){
                    vendedorForeach.checked = false;
                    this.arrVendedores.push(vendedorForeach);
                }
            });
        });

        this.$scope.$on('crm_listadavezvendedores_submitted', (event: any, args: any) => {
            let mensagem = this.entity.listadavezvendedor ? 
                'Sucesso ao atualizar Lista de vendedores!' :
                'Sucesso ao inserir Lista de vendedores!';

            this.toaster.pop({
                type: 'success',
                title: mensagem
            });

            this.busySave = false;
            this.$uibModalInstance.close();
        });
        this.$scope.$on('crm_listadavezvendedores_submit_error', (event: any, args: any) => {
            this.busySave = false;
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.entityService.save(this.entity);
                }
            } else {
                if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    if (args.response.data.message === 'Validation Failed') {
                        let distinct = (value: any, index: any, self: any) => {
                            return self.indexOf(value) === index;
                        };
                        args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                        let message = this.utilService.parseValidationMessage(args.response.data);
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: 'Erro de Validação',
                                body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                bodyOutputType: 'trustedHtml'
                            });
                    } else {
                        this.toaster.pop({
                            type: 'error',
                            title: args.response.data.message
                        });
                    }
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Ocorreu um erro ao inserir Lista de vendedores.'
                    });
                }
            }

        });
    }

    /**
     * Fecha a modal
     */
    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Apresenta blobo de adição de vendedores
     */
    abrirAdicaoVendedor(){
        if (!this.adicionandoVendedores) {
            this.adicionandoVendedores = true;
            this.arrVendedores = [];
            this.serviceVendedores.reload();
        }
    }

    /**
     * Cancela a adição de vendedores
     */
    cancelarAdicaoVendedor(){
        this.adicionandoVendedores = false;
    }

    /**
     * Adiciona vendedores selecionados a lista
     */
    adicionarVendedores(){
        if (!this.entity.itens) {
            this.entity.itens = [];
        }
        // Adiciono vendedores selecionados
        this.arrVendedores.forEach((vendedorForeach) => {
            if (vendedorForeach.checked) {
                let vendedoritem: IListadavezvendedoritem = {
                    vendedor: vendedorForeach
                }
                this.entity.itens.push(vendedoritem);
            }
        });

        // Fecho bloco de adição de vendedores
        this.adicionandoVendedores = false;
        this.arrVendedores = [];
    }

    /**
     * Retorna se o service da lista de vendedores está buscando alguma informação
     */
    private isServiceVendedoresBusy(): boolean {
        return this.serviceVendedores.loadParams.busy;
    }

    /**
     * Faz a busca de vendedores
     * @param filter 
     */
    private search(filter: any = {}){
        filter.search = this.filterVendedores;
        let entities = this.serviceVendedores.search(filter);

        return entities;
    }

    /**
     * Retorna os itens selecionados na tela
     */
    private getListavendedoritemselecionados(){
        let arrSelecionados: IListadavezvendedoritem[] = this.entity.itens.filter((itemFilter) => {
            return itemFilter.checked;
        });

        return arrSelecionados;
    }

    /**
     * Exclui itens selecionados
     */
    private excluirItens(){
        this.entity.itens = [... this.entity.itens.filter((itemFilter) => {
            return !itemFilter.checked;
        })];
    }

    /**
     * Move o item selecionado para uma posição abaixo
     */
    private moverListaItemUp(){
        // Se não tiver apenas um item selecionado, saio da função
        if (this.getListavendedoritemselecionados().length != 1) {
            return;
        }

        let indice = this.entity.itens.findIndex((itemIndex) => {
            return itemIndex.checked;
        });

        // Se já não estiver no primeiro item e tiver mais que um item
        if (indice > 0 && this.entity.itens.length > 1) {
            this.entity.itens = [... this.mudarPosicaoArray(this.entity.itens, indice, indice - 1)];
        }
    }

    /**
     * Move o item selecionado para uma posição acima
     */
    private moverListaItemDown(){
        // Se não tiver apenas um item selecionado, saio da função
        if (this.getListavendedoritemselecionados().length != 1) {
            return;
        }
        
        let indice = this.entity.itens.findIndex((itemIndex) => {
            return itemIndex.checked;
        });

        // Se já não estiver no último item
        if (indice != (this.entity.itens.length -1)) {
            this.entity.itens = [... this.mudarPosicaoArray(this.entity.itens, indice, indice + 1)];
        }
    }

    /**
     * Muda a posição de um item no array
     * @param arr 
     * @param from 
     * @param to 
     */
    private mudarPosicaoArray(arr: any[], from: number, to: number){
        arr.splice(to, 0, arr.splice(from, 1)[0]);
        return arr;
    }

    /**
     * Salva lista de vendedores
     */
    private salvarLista(){
        // Atualizo campo de posição
        this.entity.itens.forEach((itemForeach, index) => {
            itemForeach.posicao = index + 1;
        });

        // Atualizar lista
        this.busySave = true;
        this.entityService.save(this.entity);
    }

    /**
     * Coloca a tela em modo de edição
     */
    private editarLista(){
        this.situacaoTela = EnumSituacaoTela.stEdicao;
    }

    /**
     * Verifica se todos os itens da lista estão selecionados
     */
    private estaoTodosSelecionados(): boolean {
        return !this.entity.itens.some((entitySome) => {
            return !entitySome.checked;
        });
    }

    /**
     * Caso pelo menos 1 item esteja selecionado, seleciona todos.
     * Se todos estiverem selecionados, desmarca todos
     */
    private selectAll() {
        const todosSelecionados = this.entity.itens.every((entitySome) => {
            return entitySome.checked;
        });

        let selecionar = true;
        
        if (todosSelecionados){
            selecionar = false;
        }

        this.entity.itens.forEach((entity) => {
            entity.checked = selecionar;
        });
    }
}

/**
 * Service da modal de listas da vez
 */
export class CrmListaDaVezListasModalService {
    /**
     * Lista de serviços injetados no controller
     */
    static $inject = [
        '$uibModal',
        'CrmListadavezvendedores'
    ];

    constructor(
        private $uibModal: any,
        private entityService: any
    ){}
    
 
    /**
     * Abre a modal de Visualização/Edição/Criação de listas da vez
     * @param entity 
     * @param parameters 
     */
    open(entity: IListadavezVendedor, parameters: any = {}) {
        return this.$uibModal.open({
            template: require('./../../Crm/Listadavezvendedores/listadavez.listas.modal.html'),
            controller: 'CrmListaDaVezListasModalController',
            controllerAs: 'crm_lstvez_listas_mdl_cntrllr',
            windowClass: '',
            resolve: {
                entity: () => {
                    if (entity != null && entity.listadavezvendedor) {
                        return this.entityService.get(entity.listadavezvendedor);
                    } else {
                        return entity;
                    }
                }
            }
        });
    }
}

enum EnumSituacaoTela {
    stVisualizacao,
    stEdicao,
    stNovo
}
