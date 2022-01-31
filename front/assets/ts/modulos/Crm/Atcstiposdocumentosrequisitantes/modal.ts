import angular = require('angular');
import { CrmAtcstiposdocumentosrequisitantes } from './../../Crm/Atcstiposdocumentosrequisitantes/factory';

export class CrmAtcstiposdocumentosrequisitantesFormModalController {

    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors', 'CrmAtcstiposdocumentosrequisitantes', '$scope', 'utilService'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
    constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any, public CrmAtcstiposdocumentosrequisitantes: CrmAtcstiposdocumentosrequisitantes, public $scope: any, public utilService: any) {
        this.action = entity.negociotipodocumentorequisitante ? 'update' : 'insert';
    }

    submit() {
        this.submitted = true;
        if (this.form.$valid) {
            this.entity.requisitantenegocio = true;
            this.CrmAtcstiposdocumentosrequisitantes.save(this.entity, false);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
    $onInit(){
        this.$scope.$on('crm_atcstiposdocumentosrequisitantes_submitted', (event: any, args: any) => {
            this.$uibModalInstance.close(args.response.data);
        });

        this.$scope.$on('crm_atcstiposdocumentosrequisitantes_submit_error', (event: any, args: any) => {
            
               if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    if ( args.response.data.message === 'Validation Failed' ) {
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
                        title: 'Ocorreu um erro ao inserir Tipo de Documento!'            });
                }
            });
       
    }
    
}

export class CrmAtcstiposdocumentosrequisitantesFormService {
    static $inject = ['CrmAtcstiposdocumentosrequisitantes', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./modal.html'),
                controller: 'CrmAtcstiposdocumentosrequisitantesFormModalController',
                controllerAs: 'crm_tcstpsdcmntsrqstnts_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.identifier) {
                           let entity = this.entityService.get(parameters.negocio, parameters.identifier);
                           return entity;
                        } else {
                           return angular.copy(subentity);
                        }
                    },
                    constructors: () => {
                        return parameters;
                    }
                }
            });
    }

}
