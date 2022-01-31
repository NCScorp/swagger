import angular = require('angular');
export class CrmAtcspendenciaslistasFormController {
    static $inject = [                                                                                                                                                                                                                'utilService', '$scope', '$stateParams', '$state', 'CrmAtcspendenciaslistas', 'toaster'
    ,
                     'entity' ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
        constructor(                                                                                                                                                                                                                public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.action = entity.negociopendencialista ? 'update' : 'insert';
          
   this.$scope.$on('crm_atcspendenciaslistas_deleted', (event: any, args: any) => {
        this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir Crm\Atcspendenciaslistas!'
        });
        this.$state.go('crm_atcspendenciaslistas', angular.extend(this.entityService.constructors));
    });

    this.$scope.$on('crm_atcspendenciaslistas_delete_error', (event: any, args: any) => {
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

    $scope.$watch('crm_tcspndncslsts_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

                entityService.constructors = {};
        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negociopendencialista') {
                entityService.constructors[i] = $stateParams[i] ?  $stateParams[i] : '';
            }
        }
                this.constructors = entityService.constructors;
                                                $scope.$on('crm_atcspendenciaslistas_submitted', (event: any, args: any) => {
                            let insertSuccess = 'Sucesso ao inserir Crm\Atcspendenciaslistas!';
                let updateSuccess = 'Sucesso ao atualizar Crm\Atcspendenciaslistas!';

                this.toaster.pop({
                    type: 'success',
                    title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
                });
                this.$state.go('crm_atcspendenciaslistas_show', angular.extend({}, this.entityService.constructors, {'negociopendencialista': args.entity.negociopendencialista}));
                    });
        $scope.$on('crm_atcspendenciaslistas_submit_error', (event: any, args: any) => {
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
                title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Atcspendenciaslistas!' : 'Ocorreu um erro ao inserir Crm\Atcspendenciaslistas!'            });
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

        this.entityService.delete(this.$stateParams.negociopendencialista, force);
    }


    }
