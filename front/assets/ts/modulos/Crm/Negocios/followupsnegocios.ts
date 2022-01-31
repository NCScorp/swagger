// import { CrmAtcsfollowupsFormNewController } from '././../../Crm/Atcsfollowups/new';
import angular = require('angular');
import { NsModalAnexoService } from './../../Ns/Followupsnegocios/modal-anexo';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';

/**
 * Controller que gerencia as ações executadas no acordeon de acompanhamentos. Pedir ajuda ao Fábio em caso de dúvidas
 */
export class NsFollowupsnegociosController {
    static $inject = [
        '$scope',
        'toaster',
        '$http',
        'nsjRouting',
        'NsFollowupsnegocios',
        'entity',
        'moment',
        'NsModalAnexoService',
        'CommonsUtilsService'
    ];

    public exibeForm: boolean = false;
    public entities: any = [];
    public arrayTimeline: any = [];
    public busyFollowupsnegocios: boolean = false;

    public boqueiaBotaoAdicionarFollowup: boolean = false;


    constructor(
        public $scope: any,
        public toaster: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public NsFollowupsnegocios: any,
        public entity: any,
        public moment: any,
        public NsModalAnexoService: NsModalAnexoService,
        public CommonsUtilsService: CommonsUtilsService
    ) {


        // this.busyFollowup = true;
        // this.carregaFollowups();
        // this.ouvirCarregaFollowups();

        // this.ouvirSalvaFollowup();
        // this.ouvirSalvaAnexo();
        // this.ouvirRemoveAnexo();

    }

    loadcontroller(){

        // Só tenta carregar os followups se o negócio já estiver criado
        if (this.entity.documento){
            
            this.busyFollowupsnegocios = true;
            this.carregaFollowups();
            this.ouvirCarregaFollowups();
    
            this.ouvirSalvaFollowup();
        }

        // this.ouvirSalvaAnexo();
        // this.ouvirRemoveAnexo();
    }

    /* Carregamento */
    $onInit() {

    }

    submit() {

    }

    /**
     * Realiza os filtros da tela, modificando o arrayTimeline
     * @param filtroTxt 
     */
    private filtrar(filtro: {
        search: string
    }) {
        this.carregaFollowups(filtro.search);
        this.ouvirCarregaFollowups();
    }

    /**
     * Faz a requisição para buscar lista de followups
     * @param filter 
     */
    carregaFollowups(search = null) {
        this.busyFollowupsnegocios = true;
        this.entities = [];
        this.NsFollowupsnegocios.constructor = {};
        this.NsFollowupsnegocios.constructors = {'proposta': this.entity.documento};
        this.NsFollowupsnegocios.filter = search;
        this.entities = this.NsFollowupsnegocios.reload();
    }

    ouvirCarregaFollowups() {
        this.$scope.$on('ns_followupsnegocios_list_finished', (event: any, args: any) => {
            this.busyFollowupsnegocios = true;

            this.montaArrayTimeline();
            this.busyFollowupsnegocios = false;

        });
    }

    ouvirSalvaFollowup() {
        this.$scope.$on('ns_followupsnegocios_submitted', (event: any, args: any) => {
            this.busyFollowupsnegocios = true;
            this.carregaFollowups();
            this.$scope.$applyAsync();
            this.mudaVisualizacaoFollowup();
            this.busyFollowupsnegocios = false;
        });
    }

    // ouvirSalvaAnexo() {      
    //     this.$scope.$on('crm_atcsfollowupsanexos_submitted', (event: any, args: any) => {  
    //         this.busyFollowupsnegocios = true;
    //         this.carregaFollowups();
    //         this.$scope.$applyAsync();
    //         this.busyFollowupsnegocios = false;          
    //     });
    // }

    // ouvirRemoveAnexo() {      
    //     this.$scope.$on('crm_atcsfollowupsanexos_deleted', (event: any, args: any) => {  
    //         this.busyFollowupsnegocios = true;
    //         this.carregaFollowups();
    //         this.$scope.$applyAsync();
    //         this.busyFollowupsnegocios = false;          
    //     });
    // }

    /*
    * Timeline - Nasajon-ui component
    */
    montaArrayTimeline() {
        // ordenando array por data decrescente 
        this.entities = this.entities.sort((val1, val2) => { return this.moment(val2.data) - this.moment(val1.data) });

        this.arrayTimeline = [];
        this.entities.forEach(entity => {
            this.arrayTimeline.push({
                id: entity.followup,
                tipo: this.getTipoComunicacao(entity.meiocomunicacao),
                data: this.CommonsUtilsService.getMomentoData(this.moment.utc(entity.data).local().format('YYYY-MM-DD')),
                hora: this.moment.utc(entity.data).local().format('HH:mm'),
                dia: "",
                atendente: entity.created_by.nome,
                mensagem: entity.historico,
                // contatoDescricao: entity.ativo ? `(Contato com : ${entity.receptor})` : `(${entity.receptor} entrou em contato)`,
                contatoDescricao: '',
                ativo: entity.ativo,
                receptor: entity.receptor,
                tipoReceptor: this.getFiguraContato(entity.figuracontato),
                interlocutor: this.entity.estabelecimento.nomefantasia,
                tipoIcone: this.getTipoComunicacaoIcone(entity.meiocomunicacao),

                numeroanexos: null,//entity.totalanexos,
                negociofollowupanexo: null,//entity.negociofollowupanexo,
                negocio: entity.proposta.documento
            });
        });
    }

    getTipoComunicacaoIcone(tipoComunicacao) {
        switch (tipoComunicacao) {
            case 1: {
                return ' fas fa-phone '
            }
            case 2: {
                return ' fas fa-phone-slash '
            }
            case 3: {
                return ' fas fa-envelope '
            }
            case 4: {
                return ' fas fa-comment '
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
                return 'Ligação não atendida'
            }
            case 3: {
                return 'E-mail'
            }
            // case 4: {
            //     return 'Presencial'
            // }
            // "outro" foi trocado por "mensagem"
            default:
                return 'Mensagem'
        }
    }

    getFiguraContato(tipoComunicacao) {
        switch (tipoComunicacao) {
            case 1: {
                return 'Cliente'
            }
            // case 2: {
            //     return 'Prestador'
            // }
            // case 3: {
            //     return 'Seguradora'
            // }
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
                url: this.nsjRouting.generate('ns_datahoraservidor_get_data_hora_servidor', false)
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
        (async () => {

            await customElements.whenDefined('nsj-accordion');
            const accordionAtendimento: any = document.querySelector('nsj-accordion#accordion-followups');
            await accordionAtendimento.open();

          })();
          this.exibeForm = !this.exibeForm;

    }

    isBusyFollowupsnegocios() {
        return this.busyFollowupsnegocios;
    }

    // abrirModalAnexos = (item:any) => {
        
    //     // verificar lentidão d carregamento requisiscao
    //     // item.negociofollowupanexo = this.negociosFollowups.get(item.negocio, item.id);

    //     let modal = this.NsModalAnexoService.open({
    //             'followup': item
    //     }, '', '');
    //     modal.result.then((entity: any) => {
    //     })
    //         .catch((error: any) => {
    //             if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
    //                 // this.busy = false;
    //                 this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //             }
    //         });
    // }

}
