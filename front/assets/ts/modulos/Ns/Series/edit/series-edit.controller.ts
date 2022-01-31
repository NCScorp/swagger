import angular = require('angular');
import { NsSeriesService } from '../series.service';

export class NsSeriesEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'nsSeriesService',
        'toaster',
        'entity'
    ];
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    private msgErro: string = '';
    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public nsSeriesService: NsSeriesService,
        public toaster: any
        , public entity: any
    ) {
        this.action = entity.serie ? 'update' : 'insert';

        this.$scope.$on('ns_series_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir Série!'
            });
            this.$state.go('ns_series', angular.extend(this.nsSeriesService.constructors));
        });

        this.$scope.$on('ns_series_delete_error', (event: any, args: any) => {
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

        $scope.$watch('ns_srs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = nsSeriesService.constructors;
        $scope.$on('ns_series_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao incluir Série!';
            let updateSuccess = 'Sucesso ao atualizar Série!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('ns_series_show', angular.extend({}, this.nsSeriesService.constructors, { 'serie': args.entity.serie }));
        });
        $scope.$on('ns_series_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.nsSeriesService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Ns\Series!' : 'Ocorreu um erro ao inserir Ns\Series!'
                        });
                }
            }

        });
    }

    submit() {
        this.form.$submitted = true;

        this.validaDados();

        if (!this.msgErro.trim()) {
            this.msgErro = 'Alguns campos do formulário apresentam erros';
        }

        if (this.form.$valid && !this.entity.$$__submitting) {
            this.nsSeriesService.save(this.entity);
        } else if (this.msgErro) {
            this.toaster.pop({
                type: 'error',
                title: this.msgErro
            });
        }
    }
    delete(force: boolean) {

        this.nsSeriesService.delete(this.$stateParams.serie, force);
    }

    validaDados() {
        this.msgErro = '';

        if (!this.entity.estabelecimento) {
            this.form.$valid = false;
            this.msgErro = 'Preencha o estabelecimento.';
            return;
        }

        if (this.entity.numero == undefined) {
            this.form.$valid = false;
            this.msgErro = 'Número deve ter 5 caracteres.';
            return;
        }

        if (this.entity.fim < this.entity.inicio) {
            this.form.$valid = false;
            this.msgErro = 'Fim não pode ser menor que o início.';
            return;
        }

        if (this.entity.fim < this.entity.proximo) {
            this.form.$valid = false;
            this.msgErro = 'Fim não pode ser menor que o próximo.';
            return;
        }
    }
}
