import angular = require('angular');
export class CrmHistoricospadraoFormController {
    static $inject = [                                                                                                                                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmHistoricospadrao', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.historicopadrao ? 'update' : 'insert';
          
   this.$scope.$on('crm_historicospadrao_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Histórico Padrão foi excluído com sucesso!'
        });
        this.$state.go('crm_historicospadrao', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_historicospadrao_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir o Histórico Padrão!'
            });
        }
    });

    $scope.$watch('crm_hstrcspdr_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                this.constructors = entityService.constructors;
                                                $scope.$on('crm_historicospadrao_submitted', (event: any, args: any) => {
                            let insertSuccess = 'O Histórico Padrão foi criado com sucesso!';
                let updateSuccess = 'O Histórico Padrão foi editado com sucesso!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_historicospadrao_show', angular.extend({}, this.entityService.constructors, {'historicopadrao': args.entity.historicopadrao}));
                    });
        $scope.$on('crm_historicospadrao_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao editar o Histórico Padrão!' : 'Ocorreu um erro ao criar o Histórico Padrão!'            });
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

        this.entityService.delete(this.$stateParams.historicopadrao, force);
    }


    }
