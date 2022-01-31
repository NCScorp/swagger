import angular = require('angular');
import { ConfiguracaoService } from '../../../inicializacao/configuracao.service';

export class CrmAtcspendenciasDefaultController {

    static $inject = [
        '$scope',
        'toaster',
        '$http',
        'nsjRouting',
        'CrmAtcspendencias',
        'utilService',
        'ConfiguracaoService',
        'moment'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public prioridadePadrao: any;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any,
        public $http: angular.IHttpService,
        public nsjRouting: any, 
        public entityService: any, 
        public utilService: any, 
        private configuracaoService: ConfiguracaoService,
        public moment: any,
    ){
    }

    async $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        if(this.action == 'insert'){
            let prioridades = await this.buscarPrioridades();
            this.buscaPrioridadePadrao(prioridades);
            this.entity.prioridade = this.prioridadePadrao;
            this.selecionaPrazosPrioridade(this.entity.prioridade);
        }

    }

    /**
     * Busca todas as prioridades cadastradas
     */
    buscarPrioridades() {

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('ns_prioridades_index')
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }


    /**
     * Buscar a prioridade padrão na lista de prioridades recebidas
     * @param prioridades 
     */
    buscaPrioridadePadrao(prioridades: any){

        prioridades.forEach(prioridade => {

            if(prioridade.prioridadepadrao == true){
                this.prioridadePadrao = prioridade;
                return;
            }

        });

    }

    /**
     * Função que inicia o input do prazo para expiração com data e hora atual
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

    public textAreaBlocked = false;

    validarCampos(): boolean {
        let valido: boolean = true;
        
        if (this.entity.temponotificaexpiracao >= this.entity.prazo) {
            valido = false;
        } 

        return valido;
    }
    submit() {
        if (this.form.$valid) {
            this.form.$valid = this.validarCampos();
        }
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.negociopendencia = response.data.negociopendencia;
                    resolve(this.entity);
                })
                    .catch((response: any) => {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let message = this.utilService.parseValidationMessage(response.data.errors.children);
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: 'Erro de Validação',
                                        body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                        bodyOutputType: 'trustedHtml'
                                    });
                            } else {
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop(
                                {
                                    type: 'error',
                                    title: 'Erro ao adicionar.'
                                });
                        }
                    });
            });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    modificaTextoDescricao(){
        if(this.entity.historicopadrao == undefined) {
            //limpando
            this.entity.texto = ''
            this.textAreaBlocked = false;
        } else {
            //preenchendo
            this.entity.texto = this.entity.historicopadrao.texto;
            this.textAreaBlocked = true;
        }
    }

    /**
     * Definindo o campo Prazo para Expiração baseado na configuração
     */
    definePrazoExpiracao(){
        this.entity['prazo'] = parseInt(this.configuracaoService.getConfiguracao('PRAZOPARAEXPIRACAOPENDENCIA'));
    }

    /**
     * Definindo o campo Notificar Expiração Faltando baseado na configuração
     */
    defineNotificarExpiracao(){
        this.entity['temponotificaexpiracao'] = parseInt(this.configuracaoService.getConfiguracao('NOTIFICAREXPIRACAOPENDENCIA'));
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
