import angular = require('angular');
import { readRule, evalString } from '../../Commons/utils';
export class CrmPropostasitensDefaultShowController {

    static $inject = [
        '$scope',
        'toaster',
        'CrmPropostasitens',
        'utilService',
        'nsjRouting',
        '$http',
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public camposcustomizadosArray: any = [];


    constructor(
        public $scope: angular.IScope,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        public nsjRouting: any,
        public $http: any
    ) {
    }
    
    $onInit() {
        //this.busy = true;

        // this.getCampoCustomizado();

        if (typeof this.entity.camposcustomizados === 'string') {
            this.entity.camposcustomizados = JSON.parse(this.entity.camposcustomizados);
        }

        //Significa que propostaitem tem endereço
        this.entity.especificarendereco = this.entity.propostasitensenderecos.length > 0 ? true : false;

        //Ordenando os endereços, para garantir que endereço de origem venha antes do endereço de destino
        if(this.entity.propostasitensenderecos.length > 1){
            this.entity.propostasitensenderecos.sort(function(a, b) {
                return parseInt(a.ordem) - parseFloat(b.ordem);
            });
        }

        //Significa que propostaitem tem datas definidas
        if((this.entity.previsaodatahorainicio != null && this.entity.previsaodatahorainicio != undefined)
            && (this.entity.previsaodatahorafim != null && this.entity.previsaodatahorafim != undefined)){
            this.entity.especificadata = true;
        }
        else{
            this.entity.especificadata = false;
        }

    }

    /**
     * @todo
     * Definir quais campos customizados devem aparecer 
     */
    async getCampoCustomizado() {
        const { data } = await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosResponsavel' }, true)
        }) as any;

        this.camposcustomizadosArray = data;
        this.busy = false;
        this.$scope.$applyAsync();

    }

    tratarVisibilidade(campos = []) {
        campos.forEach(campo => {
            if (typeof campo.visible === 'string') {
                const regraVisibilidade = readRule(campo.visible, /^area|^possuiseguradora|^negocioarea/, this.rulesAttr);
                campo.visible = evalString(this, regraVisibilidade);
            }

            if (campo.objeto && campo.objeto.length > 0) {
                this.tratarVisibilidade(campo.objeto);
            }
        })

        return campos;
    }

    // $onInit() {
    //     this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
    //         if (newValue !== oldValue) {
    //             this.form.$setDirty();
    //         }
    //     }, true);
    // }
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.propostaitem = response.data.propostaitem;
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