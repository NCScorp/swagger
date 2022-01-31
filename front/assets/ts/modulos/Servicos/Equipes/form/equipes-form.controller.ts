import angular = require('angular');
import { EquipesService } from '../equipes.service';
export class EquipesFormController {

    static $inject = ['$scope', 'toaster', 'equipesService', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(public $scope: angular.IScope, public toaster: any, public equipesService: EquipesService, public utilService: any) {
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
                this.equipesService._save(this.entity).then((response: any) => {
                    this.entity.equipetecnico = response.data.equipetecnico;
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

    adicionar(item: any, form: any) {

        if (!this.entity.equipetecnicotecnico) this.entity.equipetecnicotecnico = [];

        if (form && item.tecnico != null) {

            if (form.$valid) {

                let tecnico: any = {};

                angular.copy(item, tecnico);
                tecnico.responsavel = this.entity.equipetecnicotecnico.length == 0;
                this.entity.equipetecnicotecnico.push(tecnico);

                // limpar formulário 
                angular.forEach(item,  (v: any, k: any) => {
                    item[k] = null;
                });

                angular.forEach(form,  (v: any, k: any) => {
                    if (!k.startsWith('$'))
                        form[k].$setPristine();
                });

                form.$setPristine();

            }
        }
    }

    verificarChecked(obj: any) {

        let lst = this.entity.equipetecnicotecnico.filter(item => item !== obj);

        lst.forEach(item => item.responsavel = false)
    }
}
