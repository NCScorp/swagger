import angular = require('angular');
import { CrmAtcspendenciasUtilsService } from './utils';

export class CrmAtcspendenciasEditController {

    static $inject = [ '$scope', 'toaster', '$http', 'nsjRouting', 'CrmAtcspendencias', 'utilService', 'moment', 'CrmAtcspendenciasUtilsService'];

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
        public $http: angular.IHttpService,
        public nsjRouting: any, 
        public entityService: any, 
        public utilService: any,
        public moment: any,
        public CrmAtcspendenciasUtilsService: CrmAtcspendenciasUtilsService,
    ) {
    }
    
    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        //Fazendo conversão da string de data recebida do backend para o objeto Date esperado no input
        let ano = parseInt(this.entity.dataprazopendencia.substring(0,4));
        let mes = parseInt(this.entity.dataprazopendencia.substring(5,7)) - 1;
        let dia = parseInt(this.entity.dataprazopendencia.substring(8,10));

        let hora = parseInt(this.entity.dataprazopendencia.substring(11,13));
        let minuto = parseInt(this.entity.dataprazopendencia.substring(14,16));

        this.entity.dataprazopendencia = new Date(ano, mes, dia);
        this.entity.dataprazopendencia.setHours(hora, minuto, 0);

    }

    /**
     * Função que carrega o input do prazo para expiração com data e hora atual
     */
     carregaDataHoraAtual(){
        this.entity.dataprazopendencia = new Date();

        //Zerando segundos e milissegundos para não considerar no input
        this.entity.dataprazopendencia.setSeconds(0,0);
    }

    /**
     * Limpa o input de prazo para expiração
     */
     limparPrazoExp(){
        this.entity.dataprazopendencia = null;
    }

    /**
     * Baseado na prioridade recebida, atribui valores aos campos prazo para expiração e notificar expiração faltando. Se não receber prioridade, limpa os campos
     * @param prioridade 
     */
    selecionaPrazosPrioridade(prioridade){

        //Se o input de data e hora estiver vazio, atribuo a data e hora atuais
        if(this.entity.dataprazopendencia == null){
            this.carregaDataHoraAtual();
        }
        
        if(prioridade){
            this.entity.prazo = prioridade.prazoexpiracao;
            // Data prazo receberá o valor dele + a quantidade de minutos do prazo de expiração da prioridade
            this.entity.dataprazopendencia = this.moment(this.entity.dataprazopendencia).add(prioridade.prazoexpiracao, 'm').toDate();
            
            //Se o tempo para notificar da prioridade for menor que 1, atribuir 1 ao campo Notificar expiração faltando
            if(prioridade.notificarexpiracaofaltando < 1){
                this.entity.temponotificaexpiracao = 1;
            }
            else{
                this.entity.temponotificaexpiracao = prioridade.notificarexpiracaofaltando;
            }
        }
        else{
            this.entity.prazo = null;

            //se está removendo a prioridade, limpo input de data e hora
            this.entity.dataprazopendencia = null;

            this.entity.temponotificaexpiracao = null;
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
