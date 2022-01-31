import angular = require('angular');
export class CrmPropostasitensFormController {
    static $inject = [                                                                                                                                                                                                                                        'CrmPropostasitensFormPropostasitensvincularfornecedorService',
                                                                                                                        'CrmPropostasitensFormPropostasitensfornecedorescolhaclienteService',
                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmPropostasitens', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                                        public CrmPropostasitensFormPropostasitensvincularfornecedorService: any,
                                                                                                                        public CrmPropostasitensFormPropostasitensfornecedorescolhaclienteService: any,
                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.propostaitem ? 'update' : 'insert';
          
   this.$scope.$on('crm_propostasitens_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Propostasitens!'
        });
        this.$state.go('crm_propostasitens', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_propostasitens_delete_error', (event: any, args: any) => {
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

    $scope.$watch('crm_prpststns_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'propostaitem') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
                this.constructors = entityService.constructors;
                                                $scope.$on('crm_propostasitens_submitted', (event: any, args: any) => {
                            let insertSuccess = 'Sucesso ao inserir Crm\Propostasitens!';
                let updateSuccess = 'Sucesso ao atualizar Crm\Propostasitens!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_propostasitens_show', angular.extend({}, this.entityService.constructors, {'propostaitem': args.entity.propostaitem}));
                    });
        $scope.$on('crm_propostasitens_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Propostasitens!' : 'Ocorreu um erro ao inserir Crm\Propostasitens!'            });
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
            delete(force: boolean) {   

        this.entityService.delete(this.$stateParams.propostaitem, force);
    }


        propostasItensVincularFornecedor(entity: any) {
                    let modal = this.CrmPropostasitensFormPropostasitensvincularfornecedorService.open({ 'propostaitem': entity.propostaitem });
            modal.result.then( () => {
                this.$state.reload();            })
            .catch( (error: any) => {
                if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                    this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                }
            });
            }
        propostasItensDesvincularFornecedor(entity: any) {
                                                    this.busy = true;
                this.entityService.propostasItensDesvincularFornecedor(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao propostasitensdesvincularfornecedor!'
                        });
                    this.$state.reload();                }).catch((response: any) => {
                    if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                        this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                    } else {
                        this.toaster.pop({
                            type: 'error',
                            title: 'Erro ao propostasitensdesvincularfornecedor.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
        propostasitensfornecedorescolhacliente(entity: any) {
                    let modal = this.CrmPropostasitensFormPropostasitensfornecedorescolhaclienteService.open({ 'propostaitem': entity.propostaitem });
            modal.result.then( () => {
                this.$state.reload();            })
            .catch( (error: any) => {
                if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                    this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                }
            });
            }
        orcamentosExcluir(entity: any) {
                                                    this.busy = true;
                this.entityService.orcamentosExcluir(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao orcamentosexcluir!'
                        });
                    this.$state.reload();                }).catch((response: any) => {
                    if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                        this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                    } else {
                        this.toaster.pop({
                            type: 'error',
                            title: 'Erro ao orcamentosexcluir.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
    }
