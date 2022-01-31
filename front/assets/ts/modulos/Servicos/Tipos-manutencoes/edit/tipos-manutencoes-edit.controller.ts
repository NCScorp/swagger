import angular = require('angular');
import { TiposManutencoesService } from '../tipos-manutencoes.service';

export class TiposManutencoesEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposManutencoesService',
        'toaster',
        'entity'
    ];

    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public tiposManutencoesService: TiposManutencoesService,
        public toaster: any,
        public entity: any
    ) {
        this.action = entity.tipomanutencao ? 'update' : 'insert';

        this.$scope.$on('tiposmanutencoes_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir tipo de manutenção!'
            });
            this.$state.go('tiposmanutencoes', angular.extend(this.tiposManutencoesService.constructors));
        });

        this.$scope.$on('tiposmanutencoes_delete_error', (event: any, args: any) => {
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

        $scope.$watch('tpsmntncs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = tiposManutencoesService.constructors;
        $scope.$on('tiposmanutencoes_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir tipo de manutenção!';
            let updateSuccess = 'Sucesso ao atualizar tipo de manutenção!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('tiposmanutencoes_show', angular.extend({}, this.tiposManutencoesService.constructors, { 'tipomanutencao': args.entity.tipomanutencao }));
        });
        $scope.$on('tiposmanutencoes_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.tiposManutencoesService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Tiposmanutencoes!' : 'Ocorreu um erro ao inserir Tiposmanutencoes!'
                        });
                }
            }

        });
    }
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.tiposManutencoesService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    delete(force: boolean) {

        this.tiposManutencoesService.delete(this.$stateParams.tipomanutencao, force);
    }


}