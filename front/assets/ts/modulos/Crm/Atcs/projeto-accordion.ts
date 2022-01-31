import angular = require('angular');
import { ISecaoController } from './classes/isecaocontroller';
/**
 * Controller que gerencia as ações executadas no acordeon de acompanhamentos. Pedir ajuda ao Fábio em caso de dúvidas
 */
export class CrmAtcsProjetoAccordionController {
    static $inject = [
        '$scope',
        'toaster',    
        '$http',
        'nsjRouting',
        'CrmAtcsfollowups',
        'entity',
        'moment',
        'secaoctrl',
        '$state'
    ];

    public labelClass: string;
    public inicializado: boolean = false;
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;
    public urlProjeto: any;

    constructor(
        public $scope: any,
        public toaster: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public atcsFollowups: any,
        public entity: any,
        public moment: any,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController,
        public $state
    ) {
        
        //Url usada na geração do link do projeto no accordion
        if(this.entity.projeto != null && this.entity.projeto.projeto != null){
            this.urlProjeto = this.$state.href('projetos_show', { projeto: this.entity.projeto.projeto });
        }
        
    }

    /* Carregamento */
    Init() {}

    verificaStatus(){
        // PROJETO_ABERTO = 0;
        // PROJETO_CANCELADO = 1;
        // PROJETO_ANDAMENTO = 2;
        // PROJETO_FINALIZADO = 3;
        // PROJETO_PARADO = 4;
        // AGUARDANDO_INICIALIZACAO = 6;

        if(this.entity.projeto && this.entity.projeto.situacao == 0){
            this.labelClass = 'label-primary'
            return 'aberto'
        }
        
        if(this.entity.projeto && this.entity.projeto.situacao == 1){
            this.labelClass = 'label-danger'
            return 'cancelado'
        }
        
        if(this.entity.projeto && this.entity.projeto.situacao == 2){
            this.labelClass = 'label-info'
            return 'andamento'
        }
        
        if(this.entity.projeto && this.entity.projeto.situacao == 3){
            this.labelClass = 'label-success'
            return 'finalizado'
        }
        
        if(this.entity.projeto && this.entity.projeto.situacao == 4){
            this.labelClass = 'label-warning'
            return 'parado'
        }

        if(this.entity.projeto && this.entity.projeto.situacao == 6){
            this.labelClass = 'label-primary'
            return 'aguardando inicialização'
        }
    }

    
}
