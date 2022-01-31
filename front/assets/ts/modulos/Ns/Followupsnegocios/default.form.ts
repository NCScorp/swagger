import angular = require('angular');
export class NsFollowupsnegociosDefaultController {

    static $inject = ['$scope', 'toaster', 'NsFollowupsnegocios', 'utilService', '$http', 'nsjRouting', 'moment'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    public textAreaBlocked = false;

    public meiosDeComunicacao: any = [];
    public figuraContatoList: any = [];

    constructor(public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any, public $http: angular.IHttpService, public nsjRouting: any, public moment: any) {
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
        this.entity.proposta = this.entity.documento;
        this.entity.followup = null;
        this.selecionaContatoUnico();
        this.defineNomeContatoReceptor();
        this.entity['realizadoemdata'] = this.moment.utc().local().format('YYYY-MM-DD');
        this.entity['realizadoemhora'] = new Date(this.moment.utc().local().format('YYYY-MM-DD HH:mm:ss'));
    }

    async selecionaContatoUnico() {
        let contatos = await this.carregaContatos();
        if (contatos.length == 1) {
            this.entity.negociocontato = contatos[0];
            this.defineNomeContatoReceptor();
        }
    }

    async carregaContatos() {
        let contatos: any = await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate(
                'crm_negocioscontatos_index',
                angular.extend({}, { documento: this.entity.documento }, { 'offset': null, 'filter': null }, null),
                true),
            timeout: null
        })
            .catch((response: any) => {
                console.log('error', response);
            });
        return contatos.data;
    }

    modificaTextoHistorico() {
        if (this.entity.historicopadrao == undefined) {
            //limpando
            this.entity.historico = ''
            this.textAreaBlocked = false;
        } else {
            //preenchendo
            this.entity.historico = this.entity.historicopadrao.texto;
            this.textAreaBlocked = true;
        }
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
                nome: 'ligação não\n atendida',
                icone: 'fas fa-phone-slash',
            },
            {
                id: 3,
                nome: 'email',
                icone: 'fas fa-envelope',
            },
            // {
            //     id: 4,
            //     nome: 'presencial',
            //     icone: 'fas fa-user-friends',
            // },
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

    /*
    * Seletor Toggle - Nasajon-ui component
    */
    getDadosSeletorToggleFiguraContato() {
        this.figuraContatoList = [
            {
                id: 1, // id para condição se é pai da lista
                value: 1, // define o valor para o input figuracontato
                nome: 'Cliente',
                // lista: [
                // {
                //     pai: 1,
                //     value: 2, // define o valor para o input figuracontato
                //     nome: 'Prestador'
                // },
                // {
                //     pai: 1,
                //     value: 3, // define o valor para o input figuracontato
                //     nome: 'Seguradora'
                // },
                // {
                //     pai: 1,
                //     value: 4, // define o valor para o input figuracontato
                //     nome: 'Outro'
                // },
                // ]
            },
            {
                id: 2, // id para condição se é pai da lista
                nome: this.entity.estabelecimento && this.entity.estabelecimento.nomefantasia, // Verificar quem entra neste campo
            }
        ];
        this.entity.figuracontato = 1;
        this.entity.ativo = false;
    }
    escolheFiguraContato(event) {
        // figuracontato sempre será o cliente, então verifico se o cliente está no "De" ou "Para" para atribuir o seu valor a figuracontato
        this.entity.figuracontato = event.detail.nomePrimeiro.id == 1 ? event.detail.nomePrimeiro.value : event.detail.nomeSegundo.value;

        // Se a comunicação estiver partindo do cliente, o campo ativo é false. Se estiver partindo do estabelecimento, é true
        if (event.detail.nomePrimeiro.id == 1) {
            this.entity.ativo = false;
        } else {
            this.entity.ativo = true;
        }
    }

    defineNomeContatoReceptor() {
        if (this.entity.negociocontato == undefined) {
            this.entity.receptor = null;
        } else {
            let nome = this.entity.negociocontato.nome + ' ' + this.entity.negociocontato.sobrenome;
            this.entity.receptor = nome;
        }
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.followup = response.data.followup;
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