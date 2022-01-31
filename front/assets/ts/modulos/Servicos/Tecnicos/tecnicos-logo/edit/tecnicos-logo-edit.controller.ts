import angular = require('angular');
export class TecnicosLogoEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tecnicosLogoService',
        'toaster',
        'entity'
    ];

    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public entityService: any,
        public toaster: any,
        public entity: any
    ) {
        this.action = entity.tecnicologo ? 'update' : 'insert'; $scope.$watch('tcncs_tcncslgs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = entityService.constructors;
        $scope.$on('tecnicos_tecnicoslogos_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir Tecnicos\Tecnicoslogos!';
            let updateSuccess = 'Sucesso ao atualizar Tecnicos\Tecnicoslogos!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('tecnicos_tecnicoslogos', angular.extend({}, this.entityService.constructors, { 'tecnicologo': args.entity.tecnicologo }));
        });
        $scope.$on('tecnicos_tecnicoslogos_submit_error', (event: any, args: any) => {
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Tecnicos\Tecnicoslogos!' : 'Ocorreu um erro ao inserir Tecnicos\Tecnicoslogos!'
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
}
