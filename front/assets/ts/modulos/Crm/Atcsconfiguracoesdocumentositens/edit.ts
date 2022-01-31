import angular = require('angular');
export class CrmAtcsconfiguracoesdocumentositensFormController {
    static $inject = [                                                                                                                                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcsconfiguracoesdocumentositens', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.atcconfiguracaodocumentoitem ? 'update' : 'insert';
          
   this.$scope.$on('crm_atcsconfiguracoesdocumentositens_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'A Configuração do Item do Documento foi excluída com sucesso!'
        });
        this.$state.go('crm_atcsconfiguracoesdocumentositens', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_atcsconfiguracoesdocumentositens_delete_error', (event: any, args: any) => {
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
                title: 'Ocorreu um erro ao excluir a Configuração do Item do Documento!'
            });
        }
    });

    $scope.$watch('crm_tcscnfgrcsdcmntstns_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                this.constructors = entityService.constructors;
                                                $scope.$on('crm_atcsconfiguracoesdocumentositens_submitted', (event: any, args: any) => {
                            let insertSuccess = 'A Configuração do Item do Documento foi criada com sucesso!';
                let updateSuccess = 'A Configuração do Item do Documento foi editada com sucesso!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_atcsconfiguracoesdocumentositens_show', angular.extend({}, this.entityService.constructors, {'atcconfiguracaodocumentoitem': args.entity.atcconfiguracaodocumentoitem}));
                    });
        $scope.$on('crm_atcsconfiguracoesdocumentositens_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao editar a Configuração do Item do Documento!' : 'Ocorreu um erro ao criar a Configuração do Item do Documento!'            });
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

        this.entityService.delete(this.$stateParams.atcconfiguracaodocumentoitem, force);
    }


    }
