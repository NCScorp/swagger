import angular = require('angular');

export class CrmAtcsareasDefaultShowController {
    static $inject = [
        '$scope',
        'toaster',
        'CrmAtcsareas',
        'utilService',
        'CrmAtcsareaspendenciaslistasFormShowService',
        'CrmAtcsareaspendenciaslistas'];

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
        public entityService: any,
        public utilService: any,
        public CrmAtcsareaspendenciaslistasFormShowService: any,
        public CrmAtcsareaspendenciaslistas: any
    ) {
        this.montaListaPendenciaslistas = this.montaListaPendenciaslistas.bind(this);
    }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        if (this.entity.negocioarea != null) {
            this.CrmAtcsareaspendenciaslistas.constructors = { 'negocioarea': this.entity.negocioarea };
            this.CrmAtcsareaspendenciaslistas.reload();
        }

        this.$scope.$on('crm_atcsareaspendenciaslistas_list_finished', (event: any, args: any) => {
            this.entity.negociosareaspendenciaslistas = args;
        });
    }

    public pendenciaslistasConfig = {
        nome: 'Listas de pendências',
        acoes: null
    }

    montaListaPendenciaslistas(subentity: any) {

        const config = {

            acoes: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.crmPendenciaslistasFormShow(subentity)
                    }
                }
            ]
        }

        return config;
    }

    crmPendenciaslistasFormShow(subentity: any) {
        let parameter = { 'atcarea': subentity.negocioarea, 'identifier': subentity.negocioareapendencialista };

        if (!subentity) {
            this.busy = true;
        }
        var modal = this.CrmAtcsareaspendenciaslistasFormShowService.open(parameter, subentity);
    }
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.negocioarea = response.data.negocioarea;
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


}
