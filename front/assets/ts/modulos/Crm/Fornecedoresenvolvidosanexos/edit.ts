import angular = require('angular');
export class CrmFornecedoresenvolvidosanexosFormController {
    static $inject = [                                                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmFornecedoresenvolvidosanexos', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.fornecedorenvolvidoanexo ? 'update' : 'insert';
          
   this.$scope.$on('crm_fornecedoresenvolvidosanexos_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Fornecedoresenvolvidosanexos!'
        });
        this.$state.go('crm_fornecedoresenvolvidosanexos', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_fornecedoresenvolvidosanexos_delete_error', (event: any, args: any) => {
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

    $scope.$watch('crm_frncdrsnvlvdsnxs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                this.constructors = entityService.constructors;
                                                $scope.$on('crm_fornecedoresenvolvidosanexos_submitted', (event: any, args: any) => {
                            let insertSuccess = 'Sucesso ao inserir Crm\Fornecedoresenvolvidosanexos!';
                let updateSuccess = 'Sucesso ao atualizar Crm\Fornecedoresenvolvidosanexos!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_fornecedoresenvolvidosanexos', angular.extend({}, this.entityService.constructors, {'fornecedorenvolvidoanexo': args.entity.fornecedorenvolvidoanexo}));
                    });
        $scope.$on('crm_fornecedoresenvolvidosanexos_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Fornecedoresenvolvidosanexos!' : 'Ocorreu um erro ao inserir Crm\Fornecedoresenvolvidosanexos!'            });
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

        this.entityService.delete(this.$stateParams.fornecedorenvolvidoanexo, force);
    }


    }
