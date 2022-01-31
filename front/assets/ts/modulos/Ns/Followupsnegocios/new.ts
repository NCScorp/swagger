import angular = require('angular');
export class NsFollowupsnegociosFormNewController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'NsFollowupsnegocios',
        'toaster',
        'entity',
        'moment'
    ];

    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    
    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public entityService: any,
        public toaster: any,
        public entity: any,
        public moment: any
    ) {
        $scope.$on('ns_followupsnegocios_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Acompanhamento foi criado com sucesso!'
            });
        });
        $scope.$on('ns_followupsnegocios_submit_error', (event: any, args: any) => {
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
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: args.response.data.message
                            });
                    }
                } else {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: 'Ocorreu um erro ao criar o Acompanhamento!'
                        });
                }
            }

        });
    }

    $onInit () {
    }

    getHoraFormatada(data) {
        if (typeof data === 'string') {
            const [hour, minute] = data.split(':');
            return this.moment({ hour, minute }).format('HH:mm');
        }

        return this.moment(data).format('HH:mm');;
    }
    limpaFollowupsnegociosId(entity) {
        entity.created_at = '';
        entity.negociofollowup = null;
    }

    submit() {
        this.form.$submitted = true;
        // if (this.form.$valid && !this.entity.$$__submitting) {
            /**
         * @Todo
         * data e hora estão sendo passadas no momento da criação 
         * retirar do backend e depois apagar essa atribuição;
         * Limpa id e created_at do entity pois estava chamando put ao invés de post
         */
        this.entity['realizadoemdata'] = this.moment.utc().local().format('YYYY-MM-DD');
        this.entity['realizadoemhora'] = new Date(this.moment.utc().local().format('YYYY-MM-DD HH:mm:ss'));
        if (this.form.$valid && !this.entity.$$__submitting
            && this.entity.figuracontato && this.entity.meiocomunicacao) {
            // this.entity.realizadoemhora = this.getHoraFormatada(new Date());
            // this.entity.realizadoemdata = this.moment(new Date());
            this.entity.realizadoemhora = null; //remove pois hoje não temos suporte a data via front
            this.entity.realizadoemdata = null; //remove pois hoje não temos suporte a data via front
            this.entity.negociocontato = null; //não envia o contato selecionado como guid. somente como texto em outro campo
            this.limpaFollowupsnegociosId(this.entity);
            this.entityService.constructors.proposta = this.entity.documento;
            this.entityService.save(this.entity);

            
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
}
