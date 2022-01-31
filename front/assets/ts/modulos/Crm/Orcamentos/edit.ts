import angular = require('angular');
export class CrmOrcamentosFormController {
    static $inject = [                                                                                                                                                                                                                                                                                                                                        'utilService', '$scope', '$stateParams', '$state', 'CrmOrcamentos', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                                                                                                                                        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.orcamento ? 'update' : 'insert';$scope.$watch('crm_rcmnts_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'orcamento') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
                this.constructors = entityService.constructors;
                                                $scope.$on('crm_orcamentos_submitted', (event: any, args: any) => {
                            let insertSuccess = 'Sucesso ao inserir Crm\Orcamentos!';
                let updateSuccess = 'Sucesso ao atualizar Crm\Orcamentos!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_orcamentos_show', angular.extend({}, this.entityService.constructors, {'orcamento': args.entity.orcamento}));
                    });
        $scope.$on('crm_orcamentos_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Orcamentos!' : 'Ocorreu um erro ao inserir Crm\Orcamentos!'            });
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
            enviar(entity: any) {
                                                    this.busy = true;
                this.entityService.enviar(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao enviar!'
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
                            title: 'Erro ao enviar.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
        aprovar(entity: any) {
                                                    this.busy = true;
                this.entityService.aprovar(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao aprovar!'
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
                            title: 'Erro ao aprovar.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
        renegociar(entity: any) {
                                                    this.busy = true;
                this.entityService.renegociar(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao renegociar!'
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
                            title: 'Erro ao renegociar.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
        reprovar(entity: any) {
                                                    this.busy = true;
                this.entityService.reprovar(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao reprovar!'
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
                            title: 'Erro ao reprovar.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
    }