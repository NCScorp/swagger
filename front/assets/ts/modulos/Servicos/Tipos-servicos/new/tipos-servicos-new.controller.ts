import angular = require('angular');
import { TiposServicosService } from '../tipos-servicos.service';

export class TiposServicosNewController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tiposServicosService',
        'toaster',
        'entity'
    ];

    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public tiposServicosService: TiposServicosService,
        public toaster: any,
        public entity: any
    ) {
        this.constructors = tiposServicosService.constructors;
        $scope.$on('tiposservicos_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao inserir tipo de serviço!'
            });
            this.$state.go('tiposservicos_show', angular.extend({}, this.tiposServicosService.constructors, { 'tiposervico': args.entity.tiposervico }));
        });
        $scope.$on('tiposservicos_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.tiposServicosService.save(this.entity);
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
                            title: 'Ocorreu um erro ao inserir Tiposservicos!'
                        });
                }
            }

        });
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.tiposServicosService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
}

