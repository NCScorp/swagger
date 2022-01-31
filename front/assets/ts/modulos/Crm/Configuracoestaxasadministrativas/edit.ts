import angular = require('angular');
export class CrmConfiguracoestaxasadministrativasFormController {
    static $inject = ['utilService', '$scope', '$stateParams', '$state', 'CrmConfiguracoestaxasadministrativas', 'toaster'
        ,
        'entity'];
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
        this.action = entity.configuracaotaxaadm ? 'update' : 'insert'; $scope.$watch('crm_cfgtxdm_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = entityService.constructors;
        $scope.$on('crm_configuracoestaxasadministrativas_submitted', (event: any, args: any) => {
            let insertSuccess = 'A Configuração de Taxa Administrativa foi criada com sucesso!';
            let updateSuccess = 'A Configuração de Taxa Administrativa foi editada com sucesso!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('crm_configuracoestaxasadministrativas_show', angular.extend({}, this.entityService.constructors, { 'configuracaotaxaadm': args.entity.configuracaotaxaadm }));
        });

        $scope.$on('crm_configuracoestaxasadministrativas_submit_error', (event: any, args: any) => {
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao editar a Configuração de Taxa Administrativa!' : 'Ocorreu um erro ao criar a Configuração de Taxa Administrativa!'
                        });
                }
            }

        });
    }

    submit() {
        this.entity.cliente = this.entity.seguradora.cliente;
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
}
