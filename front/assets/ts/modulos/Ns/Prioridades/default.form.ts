import angular = require('angular');
export class NsPrioridadesDefaultController {

    static $inject = ['$scope', 'toaster', 'NsPrioridades', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    arrCores: any[] = [];

    constructor(public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
    }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        //Cores que estarão na paleta
        this.arrCores = [
            {'codigo': EnumPrioridadesCor.prioVermelho, 'nome': 'Vermelho', classe:"label-prioridade-vermelho"},
            {'codigo': EnumPrioridadesCor.prioLaranja, 'nome': 'Laranja', classe:"label-prioridade-laranja"},
            {'codigo': EnumPrioridadesCor.prioAbobora, 'nome': 'Abóbora', classe:"label-prioridade-abobora"},
            {'codigo': EnumPrioridadesCor.prioAmarelo, 'nome': 'Amarelo', classe:"label-prioridade-amarelo"},
            {'codigo': EnumPrioridadesCor.prioVerdeClaro, 'nome': 'Verde Claro', classe:"label-prioridade-verdeclaro"},
            {'codigo': EnumPrioridadesCor.prioVerde, 'nome': 'Verde', classe:"label-prioridade-verde"},
            {'codigo': EnumPrioridadesCor.prioAzulClaro, 'nome': 'Azul Claro', classe:"label-prioridade-azulclaro"},
            {'codigo': EnumPrioridadesCor.prioAzul, 'nome': 'Azul', classe:"label-prioridade-azul"},
            {'codigo': EnumPrioridadesCor.prioRoxo, 'nome': 'Roxo', classe:"label-prioridade-roxo"},
            {'codigo': EnumPrioridadesCor.prioRosa, 'nome': 'Rosa', classe:"label-prioridade-rosa"},
            {'codigo': EnumPrioridadesCor.prioPreto, 'nome': 'Preto', classe:"label-prioridade-preto"},
            {'codigo': EnumPrioridadesCor.prioCinza1, 'nome': 'Cinza 1', classe:"label-prioridade-cinza1"},
            {'codigo': EnumPrioridadesCor.prioCinza2, 'nome': 'Cinza 2', classe:"label-prioridade-cinza2"},
            {'codigo': EnumPrioridadesCor.prioCinza3, 'nome': 'Cinza 3', classe:"label-prioridade-cinza3"},
            {'codigo': EnumPrioridadesCor.prioCinza4, 'nome': 'Cinza 4', classe:"label-prioridade-cinza4"},
        ];

        // Para na inserção vir com a primeira cor selecionada
        this.entity.cor = this.entity.cor != null ? this.entity.cor : "#CA0813";

    }

    /**
     * Setando na entidade o código da cor que será salvo no banco
     * @param cor 
     */
     selecionaCorPaleta(cor: any){
        this.entity.cor = cor.codigo;
    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.prioridade = response.data.prioridade;
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

export enum EnumPrioridadesCor {
    prioVermelho = "#CA0813",
    prioLaranja = "#FC571F",
    prioAbobora = "#FD9827",
    prioAmarelo = "#FEE433",
    prioVerdeClaro = "#85E42B",
    prioVerde = "#1FCA5D",
    prioAzulClaro = "#23CFFD",
    prioAzul = "#165AF1",
    prioRoxo = "#8E1ED3",
    prioRosa = "#E41AAB",
    prioPreto = "#1D1D1D",
    prioCinza1 = "#4A4A4A",
    prioCinza2 = "#767676",
    prioCinza3 = "#A3A3A3",
    prioCinza4 = "#D0D0D0"
}
