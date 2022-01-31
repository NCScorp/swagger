import angular = require('angular');
import { TecnicosLogoModalService } from '../tecnicos-logo/modal/tecnicos-logo-modal.service';
import { TecnicosService } from '../tecnicos.service';

export class TecnicosEditController {
    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'tecnicosService',
        'toaster',
        'entity',
        'tecnicosLogoModalService'
    ];

    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    private logopadrao = 'https://s3-sa-east-1.amazonaws.com/imagens.nasajon/geral/avatar-3x4.svg';

    constructor(
        public utilService: any,
        public $scope: angular.IScope,
        public $stateParams: angular.ui.IStateParamsService,
        public $state: angular.ui.IStateService,
        public tecnicosService: TecnicosService,
        public toaster: any,
        public entity: any,
        public tecnicosLogoModalService: TecnicosLogoModalService
    ) {
        this.action = entity.tecnico ? 'update' : 'insert';
        this.entity.pathlogo = this.entity.pathlogo ? this.entity.pathlogo : this.logopadrao;

        this.$scope.$on('tecnicos_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao excluir técnico!'
            });
            this.$state.go('tecnicos', angular.extend(this.tecnicosService.constructors));
        });

        this.$scope.$on('tecnicos_delete_error', (event: any, args: any) => {
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

        $scope.$watch('tcncs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = tecnicosService.constructors;
        $scope.$on('tecnicos_submitted', (event: any, args: any) => {
            let insertSuccess = 'Sucesso ao inserir técnico!';
            let updateSuccess = 'Sucesso ao atualizar técnico!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('tecnicos_show', angular.extend({}, this.tecnicosService.constructors, { 'tecnico': args.entity.tecnico }));
        });

        $scope.$on('tecnicos_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.tecnicosService.save(this.entity);
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Tecnicos!' : 'Ocorreu um erro ao inserir Tecnicos!'
                        });
                }
            }

        });
    }

    /**
     * Abre a modal para upload da logo do técnico
     */
    abrirModalUploadLogo(){
        let modal = this.tecnicosLogoModalService.open({}, this.entity);
        modal.result.then( (retorno: any) => {
            if (retorno.tipo == "sucesso"){
                this.entity.pathlogo = retorno.urlImagem
            }
        })
        .catch( (error: any) => {});
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.tecnicosService.save(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    delete(force: boolean) {

        this.tecnicosService.delete(this.$stateParams.tecnico, force);
    }


}
