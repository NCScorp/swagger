import angular = require('angular');
export class CrmNegociosFormNewController {
    static $inject = [                                                                                                                                                                                                'CrmNegociosFormDefaultService',
                                                                                'CrmNegociosFormQualificacaoService',
                                                                                'CrmNegociosFormDesqualificacaoService',
                                                        'utilService', '$scope', '$stateParams', '$state', 'CrmNegocios', 'toaster'
    , 'entity' ];
    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                public CrmNegociosFormDefaultService: any,
                                                                                public CrmNegociosFormQualificacaoService: any,
                                                                                public CrmNegociosFormDesqualificacaoService: any,
                                                        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'documento') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
                this.constructors = entityService.constructors;
                        $scope.$on('$destroy', () => {
            if ( this.entityService.loading_deferred ) {
                this.entityService.loading_deferred.resolve();
            }
        });
                $scope.$on('crm_negocios_submitted', (event: any, args: any) => {
                            this.toaster.pop({
                    type: 'success',
                    title: 'O Negócio foi criado com sucesso!'
                });
                this.$state.go('crm_negocios_show', angular.extend({}, this.entityService.constructors, {'documento': args.entity.documento}));
                    });
        $scope.$on('crm_negocios_submit_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao criar o Negócio!'            });
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
    }    preNegocioQualificar(entity: any) {
                    let modal = this.CrmNegociosFormQualificacaoService.open({ 'documento': entity.documento });
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
        preNegocioDesqualificar(entity: any) {
                    let modal = this.CrmNegociosFormDesqualificacaoService.open({ 'documento': entity.documento });
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
    }
