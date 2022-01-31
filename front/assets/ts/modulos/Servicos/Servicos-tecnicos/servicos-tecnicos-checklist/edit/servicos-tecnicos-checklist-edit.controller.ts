import angular = require('angular');
import { ServicosTecnicosChecklistService } from '../servicos-tecnicos-checklist.service';
export class ServicosTecnicosChecklistEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'servicosTecnicosChecklistService',
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
        public servicosTecnicosChecklistService: ServicosTecnicosChecklistService,
        public toaster: any,
        public entity: any
    ) {
        this.action = entity.servicotecnicochecklist ? 'update' : 'insert';

        this.$scope.$on('servicostecnicos_servicostecnicoschecklist_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir Servicostecnicos\Servicostecnicoschecklist!'
            });
            this.$state.go('servicostecnicos_servicostecnicoschecklist', angular.extend(this.servicosTecnicosChecklistService.constructors));
        });

        this.$scope.$on('servicostecnicos_servicostecnicoschecklist_delete_error', (event: any, args: any) => {
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

        $scope.$watch('srvcstcncs_srvcstcncschcklst_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = servicosTecnicosChecklistService.constructors;
        $scope.$on('servicostecnicos_servicostecnicoschecklist_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir Servicostecnicos\Servicostecnicoschecklist!';
            let updateSuccess = 'Sucesso ao atualizar Servicostecnicos\Servicostecnicoschecklist!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('servicostecnicos_servicostecnicoschecklist', angular.extend({}, this.servicosTecnicosChecklistService.constructors, { 'servicotecnicochecklist': args.entity.servicotecnicochecklist }));
        });
        $scope.$on('servicostecnicos_servicostecnicoschecklist_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.servicosTecnicosChecklistService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Servicostecnicos\Servicostecnicoschecklist!' : 'Ocorreu um erro ao inserir Servicostecnicos\Servicostecnicoschecklist!'
                        });
                }
            }

        });
    }
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.servicosTecnicosChecklistService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    delete(force: boolean) {

        this.servicosTecnicosChecklistService.delete(this.$stateParams.servicotecnicochecklist, force);
    }


}
