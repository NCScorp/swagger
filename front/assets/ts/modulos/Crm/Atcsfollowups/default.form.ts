import angular = require('angular');
import { ModalConfirmacaoHistoricoService } from './modal-confirmacao-historico';
export class CrmAtcsfollowupsDefaultController {

    static $inject = ['$scope', 'toaster', 'CrmAtcsfollowups', 'utilService', 'ModalConfirmacaoHistoricoService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public temHistoricoSelecionado: boolean = false;

    // Variável para armazenar o historico padrao, no caso do usuario não confirmar a troca
    public historicoPadraoOld: any = null;

    /**
     * Variável que controla quando pode abrir modal de confirmação
     */
    public abreModal: boolean = true;

    public meiosDeComunicacao: any = [];
    public figuraContatoList: any = [];
    constructor(public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any, public ModalConfirmacaoHistoricoService: ModalConfirmacaoHistoricoService) {
    }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
        this.abrirAccordionAcompanhamento();
        this.getDadosSeletorListMeiosDeComunicacao();
        this.getDadosSeletorToggleFiguraContato();
        this.entity.realizadoemdata = null;
        this.entity.receptor = null;
        this.entity.historico = null;
        this.entity.historicopadrao = null;
        this.entity.meiocomunicacao = null;
        this.entity.historicoselecionado = this.temHistoricoSelecionado;
        this.entity.historicoPadraoOld = this.historicoPadraoOld;
    }

    /**
     * Função que modifica as observações do acompanhamento, caso haja confirmação do usuário. Se não houver, volto com o valor do histórico padrão anterior
     * @param acao Indica se irá modificar o texto da observação
     */
    modificaTextoHistorico(acao: boolean) {

        // Se não houver confirmação na modal
        if(!acao){
            this.entity.historicopadrao = this.entity.historicoPadraoOld;
            this.abreModal = false;
        }
        // Se houver confirmação
        else{
            this.entity.historicoselecionado = true;
            this.entity.historicoPadraoOld = this.entity.historicopadrao;
    
            // Se estiver selecionando histórico padrão, utilizo o valor dele para preencher o campo observações
            if(this.entity.historicopadrao != null){
    
                //preenchendo observações com o texto do histórico padrão selecionado
                this.entity.historico = this.entity.historicopadrao.texto;
            }
        }

    }

    /**
     * Abre a modal de confirmação de mudança de histórico padrão
     * @param entity 
     */
    confirmaMudancaHistoricoPadrao(entity: any){

        // Se estou removendo o histórico padrão, seto a propriedade historicoselecionado para false e removo o historicoPadraoOld
        if(entity.historicopadrao == null){
            this.entity.historicoselecionado = false;
            this.entity.historicoPadraoOld = null;
        }

        // Só abro a modal de confirmação caso esteja selecionando histórico padrão e tenha texto digitado nas observações
        if(this.abreModal && entity.historicopadrao && entity.historico != null){

            let modal = this.ModalConfirmacaoHistoricoService.open(this.entity);

            modal.result.then((response: any) => {

                // Se não confirmar a mudança (response == 'manter'), volto com o valor anterior para o historico padrao
                if(response == 'manter'){
                    this.modificaTextoHistorico(false);
                }
                else{ 
                    this.modificaTextoHistorico(true);

                }

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
        }

        // Se houver histórico selecionado e não houver texto digitado, pode modificar
        else if (entity.historicopadrao && entity.historico == null){
            this.modificaTextoHistorico(true);
        }

        // Ao final de todas as verificações, modal poderá ser aberta novamente
        this.abreModal = true;

    }

    /*
    * Seletor List - Nasajon-ui component
    */
    getDadosSeletorListMeiosDeComunicacao() {
        // No banco: -- 1 - Telefone, 2 - Email, 3 - Pessoalmente, 4 - Outro
        this.meiosDeComunicacao = [
            {
                id: 1,
                nome: 'ligação',
                icone: 'fas fa-phone',
            },
            {
                id: 2,
                nome: 'email',
                icone: 'fas fa-envelope',
            },
            {
                id: 3,
                nome: 'presencial',
                icone: 'fas fa-user-friends',
            },
            {
                id: 4,
                nome: 'mensagem',
                icone: 'fas fa-comment',
            }
        ];
    }

    escolherMeioComunicacao(event: any) {
        this.entity.meiocomunicacao = event.detail.escolhido.id;
    }

    abrirAccordionAcompanhamento() {
        (async () => {
            await customElements.whenDefined('nsj-accordion');
            const nsjAcordionElement: any = document.querySelector('nsj-accordion[headline="Acompanhamento"]');
            await nsjAcordionElement.open();
        })();
    }

    /*
    * Seletor Toggle - Nasajon-ui component
    */
    getDadosSeletorToggleFiguraContato() {
        this.figuraContatoList = [
            {
                
                id: 2, // id para condição se é pai da lista
                nome: this.entity.estabelecimento && this.entity.estabelecimento.nomefantasia, // Verificar quem entra neste campo

            },
            {

                id: 1, // id para condição se é pai da lista
                value: 1, // define o valor para o input figuracontato
                nome: 'Cliente',
                lista: [
                    {
                        pai: 1,
                        value: 2, // define o valor para o input figuracontato
                        nome: 'Prestador'
                    },
                    {
                        pai: 1,
                        value: 3, // define o valor para o input figuracontato
                        nome: 'Seguradora'
                    },
                    {
                        pai: 1,
                        value: 4, // define o valor para o input figuracontato
                        nome: 'Colaborador'
                    },
                    {
                        pai: 1,
                        value: 5, // define o valor para o input figuracontato
                        nome: 'Responsável'
                    },
                    {
                        pai: 1,
                        value: 6, // define o valor para o input figuracontato
                        nome: 'Outro'
                    },
                ]
            }
        ];
        this.entity.figuracontato = 1;
        this.entity.ativo = true; //Contato por padrão parte do estabelecimento
    }

    escolheFiguraContato(event) {
        this.entity.figuracontato = event.detail.nomePrimeiro.lista ? event.detail.nomePrimeiro.value : event.detail.nomeSegundo.value;

        if (event.detail.nomePrimeiro.lista) {
            this.entity.ativo = false;
        } else {
            this.entity.ativo = true;
        }
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.negociofollowup = response.data.negociofollowup;
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
