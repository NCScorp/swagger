import angular = require('angular');
// import { CrmAtcsFormNewController } from './../../Crm/Atcs/new';
import { ValidacoesCRM } from "./ValidacoesCRM";

export class CrmAtcsFormNewController {

    static $inject = ['CrmAtcsFormContratoService',
        'CrmAtcsFormContratoexcluirService',
        'CrmAtcsFormBaixardocumentoService',
        'CrmAtcsFormEnviaratendimentoemailService',
        'CrmAtcsFormRotasgoogledirectionsService',
        'utilService', '$scope', '$stateParams', '$state', 'CrmAtcs', 'toaster'
        , 'entity'];

    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(public CrmAtcsFormContratoService: any,
        public CrmAtcsFormContratoexcluirService: any,
        public CrmAtcsFormBaixardocumentoService: any,
        public CrmAtcsFormEnviaratendimentoemailService: any,
        public CrmAtcsFormRotasgoogledirectionsService: any,
        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
        , public entity: any) {

        entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negocio') {
                entityService.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
            }
        }
        this.constructors = entityService.constructors;
        $scope.$on('$destroy', () => {
            if (this.entityService.loading_deferred) {
                this.entityService.loading_deferred.resolve();
            }
        });
        $scope.$on('crm_atcs_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Atendimento Comercial foi criado com sucesso!'
            });
            this.$state.go('crm_atcs_show', angular.extend({}, this.entityService.constructors, { 'negocio': args.entity.negocio }));
        });
        $scope.$on('crm_atcs_submit_error', (event: any, args: any) => {
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
                            title: 'Ocorreu um erro ao inserir Atendimento Comercial!'
                        });
                }
            }

        });
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

    submit() {
        this.form.$submitted = true
        /* sobrescrito para tratamento de possiveis erros dos responsaveis financeiros */
        let mensagem = ValidacoesCRM.responsaveisFinanceiros(this.entity);
        if (this.form.$valid && !this.entity.$$__submitting && !mensagem) {
          /* sobrescrito por causa dos campos customizados */
          this.entity.camposcustomizados = JSON.stringify(this.entity.camposcustomizados);
          /* sobrescrito por causa do combo para salvar localização */
        //   this.entity.localizacaosalvar = this.entity.localizacaonovasalvar != null ? this.entity.localizacaonovasalvar : null;
          this.entityService.save(this.entity);
        } else {
          mensagem = (mensagem == null) ? "Alguns campos do formulário apresentam erros." : mensagem;
          this.toaster.pop({
            type: 'error',
            title: mensagem
          });
        }
      }
    
    geraContrato(entity: any) {
        let modal = this.CrmAtcsFormContratoService.open({ 'negocio': entity.negocio });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    excluiContrato(entity: any) {
        let modal = this.CrmAtcsFormContratoexcluirService.open({ 'negocio': entity.negocio });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    atcStatusNovo(entity: any) {
        this.busy = true;
        this.entityService.atcStatusNovo(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatusnovo!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatusnovo.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    atcStatusEmAtendimento(entity: any) {
        this.busy = true;
        this.entityService.atcStatusEmAtendimento(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatusematendimento!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatusematendimento.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    atcStatusFechado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusFechado(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatusfechado!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatusfechado.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    atcStatusReaberto(entity: any) {
        this.busy = true;
        this.entityService.atcStatusReaberto(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatusreaberto!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatusreaberto.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    atcStatusFinalizado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusFinalizado(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatusfinalizado!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatusfinalizado.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    atcStatusCancelado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusCancelado(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatuscancelado!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatuscancelado.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    atcStatusDescancelado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusDescancelado(entity)
            .then((response: any) => {
                this.toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao atcstatusdescancelado!'
                });
                this.$state.reload();
            }).catch((response: any) => {
                if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                    this.toaster.pop(
                        {
                            type: 'error',
                            title: response.data.message
                        });
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao atcstatusdescancelado.'
                    });
                }
            }).finally((response: any) => {
                this.busy = false;
            });
    }

    baixardocumento(entity: any) {
        let modal = this.CrmAtcsFormBaixardocumentoService.open({ 'negocio': entity.negocio });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    enviaratendimentoemail(entity: any) {
        let modal = this.CrmAtcsFormEnviaratendimentoemailService.open({ 'negocio': entity.negocio });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    indexRotasGoogleDirections(entity: any) {
        let modal = this.CrmAtcsFormRotasgoogledirectionsService.open({ 'negocio': entity.negocio });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }
}

