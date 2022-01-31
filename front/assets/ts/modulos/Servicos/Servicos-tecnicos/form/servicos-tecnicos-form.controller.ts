import angular = require('angular');
import { ServicosTecnicosService } from '../servicos-tecnicos.service';

export class ServicosTecnicosFormController {

    static $inject = ['$scope', 'toaster', 'servicosTecnicosService', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any, 
        public servicosTecnicosService: ServicosTecnicosService, 
        public utilService: any
        ) {
    }
    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
    }
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.servicosTecnicosService._save(this.entity).then((response: any) => {
                    this.entity.servicotecnico = response.data.servicotecnico;
                    resolve(this.entity);
                })
                    .catch((response: any) => {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let message = this.utilService.parseValidationMessage(response.data.errors.children);
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
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop(
                                {
                                    type: 'error',
                                    title: 'Erro ao adicionar.'
                                });
                        }
                    });
            });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    uniqueValidation(params: any): any {
        if (this.collection) {
            let validation = { 'unique': true };
            for (var item in this.collection) {
                if (this.collection[item][params.field] === params.value) {
                    validation.unique = false;
                    break;
                }
            }
            return validation;
        } else {
            return null;
        }
    }

    /**
     * Verifica se a descrição do checklist do serviço técnico está preenchido. Caso esteja, adiciona ao objectlist de checklist
     * @param oblistctrl Object List de checklist do serviço técnico
     * @returns 
     */
    verificaChecklist(oblistctrl: any){

        //Se a descrição do checklist não estiver preenchida, não adiciono a listagem
        if (oblistctrl.form.descricao.$modelValue == null){
            return;
        }

        //Se estiver preenchida, chamo o método add do objectlist do checklist do serviço técnico
        else{
            oblistctrl.add();
        }

    }

}