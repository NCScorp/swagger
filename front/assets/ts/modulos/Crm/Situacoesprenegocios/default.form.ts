import angular = require('angular');
export class CrmSituacoesprenegociosDefaultController {

    static $inject = ['$scope', 'toaster', 'CrmSituacoesprenegocios', 'utilService'];

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
            {'codigo': EnumSituacaoPreNegocioCor.spnVermelho, 'nome': 'Vermelho', classe:"label-situacaoprenegocio-vermelho"},
            {'codigo': EnumSituacaoPreNegocioCor.spnLaranja, 'nome': 'Laranja', classe:"label-situacaoprenegocio-laranja"},
            {'codigo': EnumSituacaoPreNegocioCor.spnAbobora, 'nome': 'Abóbora', classe:"label-situacaoprenegocio-abobora"},
            {'codigo': EnumSituacaoPreNegocioCor.spnAmarelo, 'nome': 'Amarelo', classe:"label-situacaoprenegocio-amarelo"},
            {'codigo': EnumSituacaoPreNegocioCor.spnVerdeClaro, 'nome': 'Verde Claro', classe:"label-situacaoprenegocio-verdeclaro"},
            {'codigo': EnumSituacaoPreNegocioCor.spnVerde, 'nome': 'Verde', classe:"label-situacaoprenegocio-verde"},
            {'codigo': EnumSituacaoPreNegocioCor.spnAzulClaro, 'nome': 'Azul Claro', classe:"label-situacaoprenegocio-azulclaro"},
            {'codigo': EnumSituacaoPreNegocioCor.spnAzul, 'nome': 'Azul', classe:"label-situacaoprenegocio-azul"},
            {'codigo': EnumSituacaoPreNegocioCor.spnRoxo, 'nome': 'Roxo', classe:"label-situacaoprenegocio-roxo"},
            {'codigo': EnumSituacaoPreNegocioCor.spnRosa, 'nome': 'Rosa', classe:"label-situacaoprenegocio-rosa"},
            {'codigo': EnumSituacaoPreNegocioCor.spnPreto, 'nome': 'Preto', classe:"label-situacaoprenegocio-preto"},
            {'codigo': EnumSituacaoPreNegocioCor.spnCinza1, 'nome': 'Cinza 1', classe:"label-situacaoprenegocio-cinza1"},
            {'codigo': EnumSituacaoPreNegocioCor.spnCinza2, 'nome': 'Cinza 2', classe:"label-situacaoprenegocio-cinza2"},
            {'codigo': EnumSituacaoPreNegocioCor.spnCinza3, 'nome': 'Cinza 3', classe:"label-situacaoprenegocio-cinza3"},
            {'codigo': EnumSituacaoPreNegocioCor.spnCinza4, 'nome': 'Cinza 4', classe:"label-situacaoprenegocio-cinza4"},
        ];

        this.entity.cor = this.entity.cor != null ? this.entity.cor : 1;

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
                    this.entity.situacaoprenegocio = response.data.situacaoprenegocio;
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

export enum EnumSituacaoPreNegocioCor {
    spnVermelho = 1,
    spnLaranja = 2,
    spnAbobora = 3,
    spnAmarelo = 4,
    spnVerdeClaro = 5,
    spnVerde = 6,
    spnAzulClaro = 7,
    spnAzul = 8,
    spnRoxo = 9,
    spnRosa = 10,
    spnPreto = 11,
    spnCinza1 = 12,
    spnCinza2 = 13,
    spnCinza3 = 14,
    spnCinza4 = 15
}