import angular = require('angular');
export class CrmComposicoesFormController {
    static $inject = ['utilService', '$scope', '$stateParams', '$state', 'CrmComposicoes', 'toaster'
        ,
        'entity'];

    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;
    public msgErro: string;

    constructor(public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
        , public entity: any) {
        this.action = entity.composicao ? 'update' : 'insert'; $scope.$watch('crm_cmpscs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.constructors = entityService.constructors;
        $scope.$on('crm_composicoes_submitted', (event: any, args: any) => {
            let insertSuccess = 'O Serviço foi criado com sucesso!';
            let updateSuccess = 'O Serviço foi editado com sucesso!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('crm_composicoes_show', angular.extend({}, this.entityService.constructors, { 'composicao': args.entity.composicao }));
        });
        $scope.$on('crm_composicoes_submit_error', (event: any, args: any) => {
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
                            title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao editar o Serviço!' : 'Ocorreu um erro ao criar o Serviço!'
                        });
                }
            }

        });
    }

    /**
     * Faz o controle da obrigatoriedade do campo serviço técnico, que não é obrigatório quando o campo serviço coringa está marcado.
     */
    validaFormulario(){

        // Verifico se o campo servico coringa está marcado. Se estiver, o campo serviço técnico não é mais obrigatório
        //Serviço técnico e nome são os únicos 2 campos que são obrigatórios no formulário. Se serviço coringa é true e nome está preenchido, formulário é válido independente do serviço técnico
        if(this.entity.servicocoringa && this.entity.nome != null){
            this.form.$valid = true;
            return;
        }
        // Se o serviço coringa não é true e o serviço técnico está vazio, formulário não é válido porque o serviço técnico é obrigatório
        else if(!this.entity.servicocoringa && this.entity.servicotecnico == null){
            this.form.$valid = false;
            return;
        }

    }

    submit() {
        this.form.$submitted = true;

        if(!this.form.$valid){
            this.validaFormulario();
        }

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
