import angular = require('angular');
import { TiposOrdensServicosService } from '../tipos-ordens-servicos.service';
export class TiposOrdensServicosEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposOrdensServicosService',
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
        public tiposOrdensServicosService: TiposOrdensServicosService,
        public toaster: any,
        public entity: any,
    ) {
        this.action = entity.tipoordemservico ? 'update' : 'insert';

        this.$scope.$on('tiposordensservicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir tipo de ordem de serviço!'
            });
            this.$state.go('tiposordensservicos', angular.extend(this.tiposOrdensServicosService.constructors));
        });

        this.$scope.$on('tiposordensservicos_delete_error', (event: any, args: any) => {
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

        $scope.$watch('tpsrdnssrvcs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = tiposOrdensServicosService.constructors;
        $scope.$on('tiposordensservicos_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir tipo de ordem de serviço!';
            let updateSuccess = 'Sucesso ao atualizar tipo de ordem de serviço!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('tiposordensservicos_show', angular.extend({}, this.tiposOrdensServicosService.constructors, { 'tipoordemservico': args.entity.tipoordemservico }));
        });
        $scope.$on('tiposordensservicos_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.tiposOrdensServicosService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Tiposordensservicos!' : 'Ocorreu um erro ao inserir Tiposordensservicos!'
                        });
                }
            }

        });
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.tiposOrdensServicosService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    delete(force: boolean) {

        this.tiposOrdensServicosService.delete(this.$stateParams.tipoordemservico, force);
    }
}

