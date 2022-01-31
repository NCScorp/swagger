import angular = require('angular');
export class NsFornecedoresFormController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'NsFornecedores',
        'toaster',
        'entity'
    ];

    public action: string = 'update';
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
        public entity: any
    ) {
        this.action = entity.fornecedor ? 'update' : 'insert'; $scope.$watch('ns_frncdrs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = entityService.constructors;
        $scope.$on('ns_fornecedores_submitted', (event: any, args: any) => {
            let insertSuccess = 'A Prestadora de Serviços foi criada com sucesso!';
            let updateSuccess = 'A Prestadora de Serviços foi editada com sucesso!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('ns_fornecedores_show', angular.extend({}, this.entityService.constructors, { 'fornecedor': args.entity.fornecedor }));
        });
        $scope.$on('ns_fornecedores_submit_error', (event: any, args: any) => {
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao editar a Prestadora de Serviços!' : 'Ocorreu um erro ao criar a Prestadora de Serviços!'
                        });
                }
            }

        });
        
        this.entity.cnpj = this.entity.cnpj.replace(/[^\d]+/g, '');
        if (this.entity.cnpj == null) {
            this.entity.cadastro = 3;
        }
        else {
            /*Sobreescrito para fazer o CPF/CNPJ aparecerem naturalmente no campo de edição */
            if (this.entity.cnpj != null && this.entity.cnpj.length > 11){
                this.entity.cadastro = 1;
            }
            if (this.entity.cnpj.length == 11) {
                this.entity.cadastro = 2;
                this.entity.cpf = this.entity.cnpj;
            }
        }
    }
    
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {

            /**
             * TO DO: Quando o front end de Prestadoras de Serviços estiver apontando para o ErpApi, essa verificação não será mais necessária.
             * Remover essa verificação e checar se o endereço é salvo corretamente no prestador
             */

            // Se a prestadora possuir endereço, faço a verificação
            if(this.entity.endereco != null && this.entity.endereco.length > 0){

                this.entity.endereco.forEach(endereco => {
                    
                    // Se não possuir o campo codigo setado e possuir ibge, uso o ibge para definir o campo codigo
                    if(endereco.municipio.codigo == null && endereco.municipio.ibge != null){
                        endereco.municipio.codigo = endereco.municipio.ibge;
                    }

                });

            }

            this.entityService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
}
