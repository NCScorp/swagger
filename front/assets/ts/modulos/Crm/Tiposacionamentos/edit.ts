import angular = require('angular');
export class CrmTiposacionamentosFormController {
    static $inject = [                                                                                                                                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmTiposacionamentos', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.tiposacionamento ? 'update' : 'insert';
          
   this.$scope.$on('crm_tiposacionamentos_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Tipo de Acionamento foi excluído com sucesso!'
        });
        this.$state.go('crm_tiposacionamentos', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_tiposacionamentos_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Tipo de Acionamento!'
            });
        }
    });

    $scope.$watch('crm_tpscnmnts_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                this.constructors = entityService.constructors;
                                                $scope.$on('crm_tiposacionamentos_submitted', (event: any, args: any) => {
                            let insertSuccess = 'O Tipo de Acionamento foi criado com sucesso!';
                let updateSuccess = 'O Tipo de Acionamento foi editado com sucesso!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_tiposacionamentos_show', angular.extend({}, this.entityService.constructors, {'tiposacionamento': args.entity.tiposacionamento}));
                    });
        $scope.$on('crm_tiposacionamentos_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao editar o Tipo de Acionamento!' : 'Ocorreu um erro ao criar o Tipo de Acionamento!'            });
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
    }
            delete(force: boolean) {   

        this.entityService.delete(this.$stateParams.tiposacionamento, force);
    }


    }
