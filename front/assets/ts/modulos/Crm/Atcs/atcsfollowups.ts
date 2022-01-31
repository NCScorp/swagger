// import { CrmAtcsfollowupsFormNewController } from '././../../Crm/Atcsfollowups/new';
import angular = require('angular');
import { CrmModalAnexoService } from './../Atcsfollowups/modal-anexo';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';
import { ISecaoController } from './classes/isecaocontroller';

/**
 * Controller que gerencia as ações executadas no acordeon de acompanhamentos. Pedir ajuda ao Fábio em caso de dúvidas
 */
export class CrmAtcsFollowupsController {
    static $inject = [
        '$scope',
        'toaster',
        '$http',
        'nsjRouting',
        'CrmAtcsfollowups',
        'entity',
        'moment',
        'CrmModalAnexoService',
        'CommonsUtilsService',
        'secaoctrl'
    ];

    public exibeForm: boolean = false;
    public entities: any = [];
    public arrayTimeline: any = [];
    public busyFollowup: boolean = false;
    public busyInicializacao: boolean = false;
    /**
     * O tamanho do array é a quantidade de skeletons que vai aparecer na tela
     */
    public arrQtdSkeletons: any[] = [];
    private qtdSkeletons: number = 3;

    public boqueiaBotaoAdicionarFollowup: boolean = false;
    public inicializado: boolean = false;
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    /**
     * Faz o controle se o accordion de acompanhamentos já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    constructor(
        public $scope: any,
        public toaster: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public atcsFollowups: any,
        public entity: any,
        public moment: any,
        public CrmModalAnexoService: CrmModalAnexoService,
        public CommonsUtilsService: CommonsUtilsService,
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

            //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar lista de acompanhamentos
            if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                this.busyInicializacao = true;
                // Chamo função que carrega dados da tela;
                this.carregaFollowups();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }

        this.arrQtdSkeletons = [];
        for (let index = 0; index < this.qtdSkeletons; index++) {
            this.arrQtdSkeletons.push(index);
        }
    }

    Init(){
        // Só tenta carregar os followups se o atendimento comercial já estiver criado
        if (this.entity && this.entity.negocio){
            this.inicializado = true;
            this.ouvirCarregaFollowups();
            this.ouvirSalvaFollowup();
            this.ouvirSalvaAnexo();
            this.ouvirRemoveAnexo();
        }
    }

    submit() {

    }

    /**
     * Faz a requisição para buscar lista de followups
     * @param filter 
     */
    carregaFollowups() {
        this.busyFollowup = true;
        this.entities = [];
        this.atcsFollowups.constructor = {};
        this.atcsFollowups.constructors['negocio'] = this.entity.negocio;
        this.entities = this.atcsFollowups.reload();

    }

    ouvirCarregaFollowups() {
        this.$scope.$on('crm_atcsfollowups_list_finished', (event: any, args: any) => {
            this.busyFollowup = true;

            this.montaArrayTimeline();
            this.busyFollowup = false;
            this.busyInicializacao = false;

            //Uma vez que o accordion foi carregado, seto a variável de controle para true
            this.accordionCarregado = true;
        });
    }

    ouvirSalvaFollowup() {
        this.$scope.$on('crm_atcsfollowups_submitted', (event: any, args: any) => {
            this.busyFollowup = true;
            this.carregaFollowups();
            this.$scope.$applyAsync();
            this.mudaVisualizacaoFollowup();
            this.busyFollowup = false;
        });
    }

    ouvirSalvaAnexo() {      
        this.$scope.$on('crm_atcsfollowupsanexos_submitted', (event: any, args: any) => {  
            this.busyFollowup = true;
            this.carregaFollowups();
            this.$scope.$applyAsync();
            this.busyFollowup = false;          
        });
    }

    ouvirRemoveAnexo() {      
        this.$scope.$on('crm_atcsfollowupsanexos_deleted', (event: any, args: any) => {  
            this.busyFollowup = true;
            this.carregaFollowups();
            this.$scope.$applyAsync();
            this.busyFollowup = false;          
        });
    }

    /*
    * Timeline - Nasajon-ui component
    */
    montaArrayTimeline() {
        // ordenando array por data decrescente 
        this.entities = this.entities.sort((val1, val2) => { return this.moment(val2.created_at) - this.moment(val1.created_at) });

        this.arrayTimeline = [];
        this.entities.forEach(entity => {
            this.arrayTimeline.push({
                id: entity.negociofollowup,
                tipo: this.getTipoComunicacao(entity.meiocomunicacao),
                data: this.CommonsUtilsService.getMomentoData(this.moment.utc(entity.created_at).local().format('YYYY-MM-DD')),
                hora: this.moment.utc(entity.created_at).local().format('HH:mm'),
                dia: "",
                atendente: entity.created_by.nome,
                mensagem: entity.historico,                
                // contatoDescricao: entity.ativo ? `(Contato com : ${entity.receptor})` : `(${entity.receptor} entrou em contato)`,
                ativo: entity.ativo,
                receptor: entity.receptor,
                tipoReceptor: this.getFiguraContato(entity.figuracontato),
                interlocutor: this.entity.estabelecimento.nomefantasia,
                tipoIcone: this.getTipoComunicacaoIcone(entity.meiocomunicacao),

                numeroanexos: entity.totalanexos,
                negociofollowupanexo: entity.negociofollowupanexo,
                atc: entity.negocio.negocio
            });
        });
    }

    getTipoComunicacaoIcone(tipoComunicacao) {
        switch (tipoComunicacao) {
            case 1: {
                return ' fas fa-phone '
            }

            case 2: {
                return ' fas fa-envelope '
            }
            case 3: {
                return ' fas fa-user-friends '
            }

            default:
                return ' fas fa-comment '
        }
    }

    getTipoComunicacao(tipoComunicacao) {
        switch (tipoComunicacao) {
            case 1: {
                return 'Ligação'
            }

            case 2: {
                return 'E-mail'
            }
            case 3: {
                return 'Presencial'
            }

            // "outro" foi trocado por "mensagem"
            default:
                return 'Mensagem'
        }
    }

    getFiguraContato(figuraContato) {
        switch (figuraContato) {
            case 1: {
                return 'Cliente'
            }

            case 2: {
                return 'Prestador'
            }

            case 3: {
                return 'Seguradora'
            }

            case 4: {
                return 'Colaborador'
            }

            case 5: {
                return 'Responsável'
            }

            default:
                return 'Outro'
        }
    }

    /**
     * Busca a data e a hora do servidor no momento da abertura do form e preenche os campos de data e hora
     * Usar esse GET para fazer o preenchimento do campo de data e hora ao abrir formulario de criação do followup com a data e a hora do servidor
     */
    carregaDataHora() {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('ns_datahoraservidor_get_data_hora_servidor', true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });

    }

    /**
     * Muda a visualização do acordeon de acompanhamento
     */
    mudaVisualizacaoFollowup() {
        this.exibeForm = !this.exibeForm;
        (async () => {

            await customElements.whenDefined('nsj-accordion');
            const accordionAtendimento: any = document.querySelector('nsj-accordion#accordion-followups');
            await accordionAtendimento.open();

          })();
    }

    isBusyFollowup() {
        return this.busyFollowup;
    }

    abrirModalAnexos = (item:any) => {
        
        // verificar lentidão d carregamento requisiscao
        // item.negociofollowupanexo = this.negociosFollowups.get(item.negocio, item.id);

        let modal = this.CrmModalAnexoService.open({
                'followup': item
        }, '', '');
        modal.result.then((entity: any) => {
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    // this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

}
