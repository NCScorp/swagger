import angular = require('angular');
export class NsPessoasLogosFormNewController {
    static $inject = [                                                                                        'utilService', '$scope', '$stateParams', '$state', 'NsPessoasLogos', 'toaster'
    , 'entity' ];
    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                this.constructors = entityService.constructors;
                        $scope.$on('ns_pessoas_logos_submitted', (event: any, args: any) => {
                            this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao inserir Ns\Pessoas\Logos!'
                });
                this.$state.go('ns_pessoas_logos', angular.extend({}, this.entityService.constructors, {'pessoalogo': args.entity.pessoalogo}));
                    });
        $scope.$on('ns_pessoas_logos_submit_error', (event: any, args: any) => {
                            if (args.response.status === 409) {
        if (confirm(args.response.data.message)) {
            this.entity[''] = args.response.data.entity[''];
            this.entityService.save(this.entity);
        }
    } else {
       if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
            if ( args.response.data.message === 'Validation Failed' ) {
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
                title: 'Ocorreu um erro ao inserir Ns\Pessoas\Logos!'            });
        }
    }

        });
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
                                this.entityService.save(this.entity);
                } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }}
