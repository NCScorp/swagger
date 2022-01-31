import angular = require('angular');
import { TecnicosLogoService } from '../tecnicos-logo.service';

export class TecnicosLogoNewController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tecnicosLogoService',
        'toaster',
        'entity'
    ];

    public action: string = 'insert';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    
    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public tecnicosLogoService: TecnicosLogoService,
        public toaster: any,
        public entity: any
    ) {
        this.constructors = tecnicosLogoService.constructors;
        $scope.$on('tecnicos_tecnicoslogos_submitted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao inserir Tecnicos\Tecnicoslogos!'
            });
            this.$state.go('tecnicos_tecnicoslogos', angular.extend({}, this.tecnicosLogoService.constructors, { 'tecnicologo': args.entity.tecnicologo }));
        });
        $scope.$on('tecnicos_tecnicoslogos_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.tecnicosLogoService.save(this.entity);
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
                            title: 'Ocorreu um erro ao inserir Tecnicos\Tecnicoslogos!'
                        });
                }
            }

        });
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.tecnicosLogoService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
}
