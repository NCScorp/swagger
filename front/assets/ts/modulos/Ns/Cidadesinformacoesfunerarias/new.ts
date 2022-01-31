import angular = require('angular');
export class NsCidadesinformacoesfunerariasFormNewController {
    static $inject = ['utilService', '$scope', '$stateParams', '$state', 'NsCidadesinformacoesfunerarias', 'toaster'
        , 'entity'];
    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    constructor(public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
        , public entity: any) {
        this.constructors = entityService.constructors;
        $scope.$on('ns_cidadesinformacoesfunerarias_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'A Cidade foi criada com sucesso!'
            });
            this.$state.go('ns_cidadesinformacoesfunerarias_show', angular.extend({}, this.entityService.constructors, { 'cidadeinformacaofuneraria': args.entity.cidadeinformacaofuneraria }));
        });
        $scope.$on('ns_cidadesinformacoesfunerarias_submit_error', (event: any, args: any) => {
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
                            title: 'Ocorreu um erro ao criar a Cidade!'
                        });
                }
            }

        });
    }

    /**
     * Sobrescrevo para fazer modificações antes de chamar a função pai
     */
    submit() {
        if (!this.entity.fornecedores) {
            this.entity.fornecedores = [];
        }
        
        //Seto propriedade ordem de acordo com o índice dos itens
        this.entity.fornecedores.forEach((fornecedor, index) => {
            fornecedor.ordem = (index + 1);    
        });

        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.entityService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    // submit() {
    //     this.form.$submitted = true;
    //     if (this.form.$valid && !this.entity.$$__submitting) {
    //         this.entityService.save(this.entity);
    //     } else {
    //         this.toaster.pop({
    //             type: 'error',
    //             title: 'Alguns campos do formulário apresentam erros'
    //         });
    //     }
    // }
}
