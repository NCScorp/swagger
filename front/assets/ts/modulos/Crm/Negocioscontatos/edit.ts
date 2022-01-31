import angular = require('angular');
export class CrmNegocioscontatosFormController {
    static $inject = [                                                                                                                                                        'CrmNegocioscontatosFormDefaultService',
                                                        'utilService', '$scope', '$stateParams', '$state', 'CrmNegocioscontatos', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                        public CrmNegocioscontatosFormDefaultService: any,
                                                        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.id ? 'update' : 'insert';
          
   this.$scope.$on('crm_negocioscontatos_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Contato do Negócio foi Excluído com sucesso!'
        });
        this.$state.go('crm_negocioscontatos', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_negocioscontatos_delete_error', (event: any, args: any) => {
        if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
            this.toaster.pop(
            {
                type: 'error',
                title: args.response.data.message
            });
        } else {
            this.toaster.pop(
            {
                type: 'error',
                title: 'Ocorreu um erro ao tentar excluir.'
            });
        }
    });

    $scope.$watch('crm_ngcscntts_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                this.constructors = entityService.constructors;
                                                $scope.$on('crm_negocioscontatos_submitted', (event: any, args: any) => {
                            let insertSuccess = 'O Contato do Negócio foi criado com sucesso!';
                let updateSuccess = 'O Contato do Negócio foi editado com sucesso!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_negocioscontatos', angular.extend({}, this.entityService.constructors, {'id': args.entity.id}));
                    });
        $scope.$on('crm_negocioscontatos_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Negocioscontatos!' : 'Ocorreu um erro ao inserir Crm\Negocioscontatos!'            });
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
                title: 'Alguns campos do formulário apresentam erros'
            });
        }
    }
            delete() {
    let modal = this.CrmNegocioscontatosFormDefaultService.open({ 'id': entity.id });
    modal.result.then(() => {
        this.entityService
                .get(this.$stateParams.documento, this.$stateParams.id)
                .then((data: any) => {
                    this.$state.go('crm_negocioscontatos', angular.extend(this.entityService.constructors));
                });
    })
    .catch((error: any) => {
                if ( error !== 'backdrop click' && error !== 'fechar' && error != 'escape key press' ) {
            this.toaster.pop({
                    type: 'error',
                    title: error
                });
        }
    });
}

    }
