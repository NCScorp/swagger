import angular = require('angular');
export class CrmComposicoesDefaultController {

    static $inject = [                         'CrmComposicoesfuncoesFormService',
                                                 'CrmComposicoesfamiliasFormService',
                         '$scope', 'toaster', 'CrmComposicoes', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public tooltipServicoCoringa: string = 'Com esta opção marcada, não é mais necessário selecionar um serviço técnico e, ao selecionar este serviço no pedido e/ou orçamento, será solicitada a sua descrição';

    constructor(                         public CrmComposicoesfuncoesFormService: any,
                                                 public CrmComposicoesfamiliasFormService: any,
                         public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
                    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
            }
       crmComposicoesfuncoesForm() {
        let modal = this.CrmComposicoesfuncoesFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.funcoes === undefined ) {
                this.entity.funcoes = [subentity];
            } else {
                this.entity.funcoes.push(subentity);
            }
        })
        .catch( (error: any) => {
            if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                this.toaster.pop({
                        type: 'error',
                        title: error
                    });
            }
        });
    }     crmComposicoesfamiliasForm() {
        let modal = this.CrmComposicoesfamiliasFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.familias === undefined ) {
                this.entity.familias = [subentity];
            } else {
                this.entity.familias.push(subentity);
            }
        })
        .catch( (error: any) => {
            if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                this.toaster.pop({
                        type: 'error',
                        title: error
                    });
            }
        });
    }                submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
        return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.composicao = response.data.composicao;
                    resolve(this.entity);
                })
                .catch((response: any) => {
                    if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                        if ( response.data.message === 'Validation Failed' ) {
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
        if ( this.collection ) {
            let validation = { 'unique' : true };
            for ( var item in this.collection ) {
                if ( this.collection[item][params.field] === params.value ) {
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
     * Faz o controle para que apenas uma opção da área Realização de Serviços possa ser marcada. Também faz o controle para permitir nenhuma opção marcada
     * @param nomeCampo 
     */
    clickRealizacaoServico(nomeCampo: string){

        // Se a opção clicada for Serviço externo
        if(nomeCampo == 'servicoexterno'){

            // Caso já está marcado e foi clicado, a propriedade servicoexterno é false para desmarcar opção
            if(this.entity.servicoexterno){
                this.entity.servicoexterno = false;
            }

            //Caso não está marcado e foi clicado, a propriedade servicoexterno é true para marcar opção
            else{
                this.entity.servicoexterno = true;
            }

            // Independente se estiver marcando ou desmarcando servicoexterno, a servicoprestadoraacionada é false
            this.entity.servicoprestadoraacionada = false;

        }

        // Se a opção clicada for serviço com prestadora acionada
        else{

            // Caso já está marcado e foi clicado, a propriedade servicoprestadoraacionada é false para desmarcar opção
            if(this.entity.servicoprestadoraacionada){
                this.entity.servicoprestadoraacionada = false;
            }

            //Caso não está marcado e foi clicado, a propriedade servicoprestadoraacionada é true para marcar opção
            else{
                this.entity.servicoprestadoraacionada = true;
            }

            // Independente se estiver marcando ou desmarcando servicoprestadoraacionada, o servicoexterno é false
            this.entity.servicoexterno = false;

        }

    }

}
