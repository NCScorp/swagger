import angular = require('angular');
// import { CrmAtcsdadosseguradorasDefaultController } from '@MDA/Crm/Atcsdadosseguradoras/default.form';
import { CrmAtcsdadosseguradoras } from './../../Crm/Atcsdadosseguradoras/factory';
import { CrmTemplatespropostas } from './../../Crm/Templatespropostas/factory';
import { CrmAtcsDadosSeguradorasFormService } from './modal';

export class CrmAtcsdadosseguradorasDefaultController {

    static $inject = [
        '$scope',
        'toaster',
        'CrmAtcsdadosseguradoras',
        'utilService',
        '$http',
        'nsjRouting',
        'CrmAtcsDadosSeguradorasFormService',
        'CrmAtcsdadosseguradoras',
        'CrmTemplatespropostas'
    ];

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
        public $http: any,
        public nsjRouting: any,
        private crmAtcsDadosSeguradorasFormService: CrmAtcsDadosSeguradorasFormService,
        private crmAtcsdadosseguradoras: CrmAtcsdadosseguradoras,
        private crmTemplatespropostas: CrmTemplatespropostas
    ) {

    }

    $onInit() {
        // usado para resetar o valor de submitted do form;
        this.form.$submitted = false;
        
        const atcsdadosseguradoras = this.entity?.negociosdadosseguradoras || [];
        this.crmAtcsdadosseguradoras.constructors = { 'negocio': this.constructors.negocio };
        this.$scope.$watch('$ctrl.entity.cliente', (cliente, clienteAntigo: any) => {
            if (atcsdadosseguradoras.length === 0 ||
                    clienteAntigo == null || 
                    // Se não mudou o cliente
                    (clienteAntigo != null && (cliente == null || cliente.cliente == null || clienteAntigo.cliente != cliente.cliente) ) ||
                    this.entity.possuiseguradora === false
                ) {

                //Ao exibir os dados do seguro, já vir com o checkbox de apólice confirmada marcado
                this.entity.negociosdadosseguradoras = [{
                    ...(new Seguradora),
                    ...{ 'negocio': this.constructors.negocio, 'seguradora': this.entity.cliente, 'apoliceconfirmada': true}
                }];
            }
        })

        this.$scope.$watch('$ctrl.form.$submitted', (submitted) => {
            if (submitted) {
                this.form.$setSubmitted(true);
            }
        });

        this.$scope.$on('crm_atcsdadosseguradoras_delete_error', (factoryEntity: any, data: any) => {
            this.toaster.pop({
                type: 'error',
                title: data.response.data.message
            });
        });

        this.$scope.$on('crm_atcsdadosseguradoras_deleted', (factoryEntity: any, data: any) => {
            if (data.response.data[1] !== undefined) {
                let index = this.entity.negociosdadosseguradoras.findIndex((element) => {
                    if (element.negociodadosseguradora == data.response.data[1]) {
                        return true;
                    }
                });

                // Apenas removo o item se ele for encontrado (se não retornar -1 no findIndex)
                if(!(index < 0)){
                    this.entity.negociosdadosseguradoras.splice(index, 1);
                }
            }

            this.toaster.pop({
                type: 'success',
                title: 'Seguro removido.'
            });

        });


    }

    /**
     * Função que atualiza o construtor do lookup de apólice. Função necessária pois havia um problema que ocorria no lookup ao ter mais de um seguro no atendimento comercial
     * @param index 
     */
    atualizaConstrutor(index: any){

        this.crmTemplatespropostas.constructors = { 'templatepropostagrupo': this.entity.negociosdadosseguradoras[index].produtoseguradora.templatepropostagrupo };

    }

    adicionarApolice() {
        //Ao adicionar uma apólice, já vir com o checkbox de apólice confirmada marcado
        this.entity.negociosdadosseguradoras.push(
            angular.copy({
                ...new Seguradora,
                ...{ 'negocio': this.constructors.negocio, 'seguradora': this.entity.cliente, 'apoliceconfirmada': true }
            })
        )
    }

    removerApolice(index) {
        const modal = this.crmAtcsDadosSeguradorasFormService.open({
            message: 'Deseja excluir este item permanentemente?',
            title: 'Excluir item'
        });
        modal.result
            .then((remover: boolean) => {
                if (remover) {
                    if (this.entity.negociosdadosseguradoras[index].negociodadosseguradora) {
                        const atcdadosseguradora = this.entity.negociosdadosseguradoras[index].negociodadosseguradora;
                        this.crmAtcsdadosseguradoras.delete(atcdadosseguradora, true);
                    } else {
                        this.entity.negociosdadosseguradoras.splice(index, 1);
                    }
                }
            })
            .catch((error) => {
                console.error(error)
            })
    }

    //Ao mudar o produto selecionado, limpa campos apólice, valor da apólice e valor autorizado
    atualizouProduto(index){
        this.entity.negociosdadosseguradoras[index].apolice = null;
        this.entity.negociosdadosseguradoras[index].valorapolice = null;
        this.entity.negociosdadosseguradoras[index].valorautorizado = null;
    }

    //Evento disparado ao mudar o valor da apólice
    atualizaValorDaApolice(index) {

        //Se estiver removendo a apólice, limpo os campos valor da apólice e valor autorizado
        if(this.entity.negociosdadosseguradoras[index].apolice == null){
            this.entity.negociosdadosseguradoras[index].valorapolice = null;
            this.entity.negociosdadosseguradoras[index].valorautorizado = null;

        }

        //Se estiver mudando a apólice e ela possuir valor, realizar verificações internas
        else if ( ( this.entity.negociosdadosseguradoras[index].apolice !== null ) && ( this.entity.negociosdadosseguradoras[index].apolice.valorapolice > 0) ) {

            //Atualizo o valor da apólice, baseado no valor interno da apólice
            this.entity.negociosdadosseguradoras[index].valorapolice = this.entity.negociosdadosseguradoras[index].apolice.valorapolice;

            //Se for uma criação, atualizo o valor autorizado baseado no valor da apólice
            if(this.entity.negociosdadosseguradoras[index].negociodadosseguradora == null){
                this.atualizaValorAutorizado(index);
            }
            //Se for uma edição, ao mudar a apólice, limpo o campo valor autorizado
            else{
                this.entity.negociosdadosseguradoras[index].valorautorizado = null;
            }

        }
        
    }

    //Função que atualiza o valor autorizado baseado no valor da apólice digitado. Não atualizo quando for uma edição
    atualizaValorAutorizado(index){

        if( this.entity.negociosdadosseguradoras[index].negociodadosseguradora == null && this.entity.negociosdadosseguradoras[index].valorapolice != null){
            this.entity.negociosdadosseguradoras[index].valorautorizado = this.entity.negociosdadosseguradoras[index].valorapolice;
        }

    }

    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
        return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.negociodadosseguradora = response.data.negociodadosseguradora;
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
}

class Seguradora {
    atc: any;
    seguradora: any;
    produtoseguradora: any;
    apolicetipo: any;
    sinistro: any;
    apoliceconfirmada: boolean;
    titularnome: string;
    titulartipodocumento: any;
    titularvinculo: any;
    nomefuncionarioseguradora: string;
    titularmatricula: string;
    valorautorizado: any;
    apolice: any;
    beneficiario: string;
}
