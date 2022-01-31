import angular = require('angular');
export class CrmAtcsdocumentosFormController {
    static $inject = [                                                                                                                                                                                                                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcsdocumentos', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.negociodocumento ? 'update' : 'insert';
          
   this.$scope.$on('crm_atcsdocumentos_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'O Documento foi excluído com sucesso!'
        });
        this.$state.go('crm_atcsdocumentos', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_atcsdocumentos_delete_error', (event: any, args: any) => {
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

    $scope.$watch('crm_tcsdcmnts_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                this.constructors = entityService.constructors;
                                                $scope.$on('crm_atcsdocumentos_submitted', (event: any, args: any) => {
                            let insertSuccess = 'O Documento foi criado com sucesso!';
                let updateSuccess = 'O Documento foi editado com sucesso!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_atcsdocumentos_show', angular.extend({}, this.entityService.constructors, {'negociodocumento': args.entity.negociodocumento}));
                    });
        $scope.$on('crm_atcsdocumentos_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Atcsdocumentos!' : 'Ocorreu um erro ao inserir Crm\Atcsdocumentos!'            });
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

        this.entityService.delete(this.$stateParams.negociodocumento, force);
    }


        recebeDocumento(entity: any) {
                                                    this.busy = true;
                this.entityService.recebeDocumento(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao recebedocumento!'
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
                            title: 'Erro ao recebedocumento.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
        preAnalise(entity: any) {
                                                    this.busy = true;
                this.entityService.preAnalise(entity)
                .then((response: any) => {
                    this.toaster.pop({
                            type: 'success',
                            title : 'Sucesso ao preanalise!'
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
                            title: 'Erro ao preanalise.'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
                                        }
    }
