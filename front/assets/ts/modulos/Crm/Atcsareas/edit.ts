import angular = require('angular');
export class CrmAtcsareasFormController {

    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'CrmAtcsareas',
        'toaster',
        'entity',
        '$http',
        'nsjRouting'];

    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    
    constructor(public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
        , public entity: any, public $http: any, public nsjRouting: any) {

        this.action = entity.negocioarea ? 'update' : 'insert';
        $scope.$watch('crm_tcsrs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = entityService.constructors;

        $scope.$on('crm_atcsareas_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir a área de atendimento comercial!';
            let updateSuccess = 'Sucesso ao atualizar a área de atendimento comercial!';
            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('crm_atcsareas_show', angular.extend({}, this.entityService.constructors, { 'negocioarea': args.entity.negocioarea }));
        });

        // this.$scope.$on('crm_atcsareas_deleted', (event: any, args: any) => {
        //     this.toaster.pop({
        //         type: 'success',
        //         title: 'A Área de Atendimento comercial foi excluída com sucesso!'
        //     });
        //     this.$state.go('crm_atcsareas', angular.extend(this.entityService.constructors));
        // });

        // this.$scope.$on('crm_atcsareas_delete_error', (event: any, args: any) => {
        //     if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
        //         this.toaster.pop(
        //             {
        //                 type: 'error',
        //                 title: args.response.data.message
        //             });
        //     } else {
        //         this.toaster.pop(
        //             {
        //                 type: 'error',
        //                 title: 'Ocorreu um erro ao excluir a Área de Atendimento comercial!'
        //             });
        //     }
        // });

        // $scope.$watch('crm_tcsrs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
        //     if (newValue !== oldValue) {
        //         this.form.$setDirty();
        //     }
        // }, true);

        // $scope.$on('crm_atcsareas_submitted', (event: any, args: any) => {
        //     let insertSuccess = 'A Área de Atendimento Comercial foi criada com sucesso!';
        //     let updateSuccess = 'A Área de Atendimento Comercial foi editada com sucesso!';

        //     this.toaster.pop({
        //         type: 'success',
        //         title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
        //     });
        //     this.$state.go('crm_atcsareas_show', angular.extend({}, this.entityService.constructors, { 'negocioarea': args.entity.negocioarea }));
        // });

        $scope.$on('crm_atcsareas_submit_error', (event: any, args: any) => {
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar a Área de atendimento comercial!' : 'Ocorreu um erro ao inserir Crm\Atcsareas!'
                        });
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
        this.entityService.delete(this.$stateParams.negocioarea, force);
    }
    /**
     */
    getAdicionados() {
        return this.entity.negociosareaspendenciaslistas.filter((lista) => {
            return lista.negocioareapendencialista == null;
        });
    }

    getRemovidos() {
        return this.entity.negociosareaspendenciaslistasoriginal.filter((original) => {
            let item = this.entity.negociosareaspendenciaslistas.filter((lista) => {
                return original.negocioareapendencialista == lista.negocioareapendencialista;
            });
            return item.length < 1;
        });
    }

    getEditados() {
        return this.entity.negociosareaspendenciaslistas.filter((lista) => {
            return lista.editado;
        });
    }

    comparaPendencias() {
        if (this.entity.negociosareaspendenciaslistas.length != this.entity.negociosareaspendenciaslistasoriginal.length) {
            return false;
        }
        else {
            for (let i = 0; i < this.entity.negociosareaspendenciaslistas; i++) {
                if (this.entity.negociosareaspendenciaslistas[i] != this.entity.negociosareaspendenciaslistasoriginal[i]) {
                    return false;
                }
            }
        }
        return true;
    }
}