import { ContratosService } from '@modules/contratos/contratos.service';
import angular = require('angular');

export class HomeController {
    static $inject = [
        '$scope',
        '$stateParams',
        '$state',
        'contratosService'
    ];

    /**
     * Mensagem de bem vindo
     */
    mensagemBemVindo: string;
    /**
     * Define se a tela está processando alguma informação
     */
    busy: boolean = false;
    /**
     * Mensagem de carregamento
     */
    busyMensagem = 'Carregando...';
    totaisContratos =  {
        total: 0,
        ativos: 0,
        aguardando_ativacao: 0
    };
    constructor(
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: any,
        public contratosService: ContratosService
    ) {}

    $onInit(){
        this.montarMensagemBemVindo();
        this.getDadosCards();
    }
    
    async getDadosCards(){
        this.setBusy();

        try {
            // Busco lista de contratos
            this.totaisContratos = await this.contratosService.getTotaisContratos();
        } catch (error) {
        } finally {
            this.busy = false;
            this.reloadScope();
        }
    }

    /**
     * Coloca a tela em modo de processamento
     * @param mensagem 
     */
    setBusy(mensagem = 'Carregando...'){
        this.busyMensagem = mensagem;
        this.busy = true;
    }

    /**
     * Atualiza escopo da tela
    */
    reloadScope(){
        this.$scope.$applyAsync();
    }

    /**
     * Monta mensagem de bem vindo
     */
    montarMensagemBemVindo(){
        this.mensagemBemVindo = 'Bem-vindo ao Monitor Financeiro!';
    }

    /**
     * Disparado ao clicar no botão de ver contratos
     */
    onBtnVerContratosClick(){
        this.$state.go('contratos');
    }
}
