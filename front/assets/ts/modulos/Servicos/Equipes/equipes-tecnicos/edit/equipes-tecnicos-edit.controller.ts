import angular = require('angular');
import { EquipesTecnicosService } from '../equipes-tecnicos.service';
export class EquipesTecnicosEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'equipesTecnicosService',
        'toaster',
        'entity',
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
        public equipesTecnicosService: EquipesTecnicosService,
        public toaster: any,
        public entity: any
    ) {
        this.action = entity.equipetecnicotecnico ? 'update' : 'insert';

        this.$scope.$on('equipes_equipestecnicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir Equipes\Equipestecnicos!'
            });
            this.$state.go('equipes_equipestecnicos', angular.extend(this.equipesTecnicosService.constructors));
        });

        this.$scope.$on('equipes_equipestecnicos_delete_error', (event: any, args: any) => {
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

        $scope.$watch('qps_qpstcncs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = equipesTecnicosService.constructors;
        $scope.$on('equipes_equipestecnicos_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir Equipes\Equipestecnicos!';
            let updateSuccess = 'Sucesso ao atualizar Equipes\Equipestecnicos!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('equipes_equipestecnicos', angular.extend({}, this.equipesTecnicosService.constructors, { 'equipetecnicotecnico': args.entity.equipetecnicotecnico }));
        });
        $scope.$on('equipes_equipestecnicos_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.equipesTecnicosService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Equipes\Equipestecnicos!' : 'Ocorreu um erro ao inserir Equipes\Equipestecnicos!'
                        });
                }
            }

        });
    }
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.equipesTecnicosService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    delete(force: boolean) {

        this.equipesTecnicosService.delete(this.$stateParams.equipetecnicotecnico, force);
    }
}
