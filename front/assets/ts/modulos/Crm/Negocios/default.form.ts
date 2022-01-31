import angular = require('angular');
import { NsClientes } from '../../Ns/Clientes/factory';
import { EnumClienteReceitaAnual, EnumClienteQualificacao, INegocioApi, ICliente, INegocioContato } from './classes';
export class CrmNegociosDefaultController {


    static $inject = [
        '$scope', 
        'toaster', 
        'CrmNegocios', 
        'utilService',
        'NsClientes'
    ];

    /**
     * Enum de Receita anual do cliente
     */
    private EnumClienteReceitaAnual = EnumClienteReceitaAnual;
    /**
     * Enum do tipo de cliente: Cpf ou Cnpj
     */
    private EnumClienteQualificacao = EnumClienteQualificacao;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    /**
     * Entidade do negócio
     */
    public entity: INegocioApi;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any, 
        public entityService: any, 
        public utilService: any,
        public NsClientes: NsClientes
    ) {
    }

    /**
     * Busco o cliente pelo CPF Ou CNPJ
     */
    private async getClienteByDocumento(ev: any): Promise<void> {

        // Inicio loading
        this.busy = true;

        let docBusca: string = ev.target.value;
        // Remove ".", "/" e "-"
        docBusca = docBusca.replace(/\.|\/|\-/g, '');
        const tamTipoDoc: number = (this.entity.cliente_qualificacao == EnumClienteQualificacao.cqCNPJ) ? 14 : 11;

        if (docBusca.length == tamTipoDoc) {
            const paramFilters = {
                'cnpj': docBusca
            };

            try {
                const arrClientes: ICliente[] = await this.NsClientes.findAll(paramFilters);

                if (arrClientes.length > 0) {
                    this.entity.cliente = arrClientes[0];
                    this.entity.cliente_codigo = this.entity.cliente.codigo;
                    this.entity.cliente_companhia = this.entity.cliente.razaosocial;
                    this.entity.cliente_nomefantasia = this.entity.cliente.nomefantasia;
                }
            } catch (error) {
                
            }
            
            this.entity.buscoucliente = true;
        }

        // Fecho loading
        this.busy = false;
        this.$scope.$applyAsync();
    }

    /**
     * Adiciona um Novo contato
     */
    private adicionarNovoContato(){
        this.entity.negocioscontatos.push({});
    }

    /**
     * Remove o contato
     * @param indice 
     */
    private removerContato(contato: INegocioContato){
        let indice = this.entity.negocioscontatos.findIndex((contatoIndex) => {
            return (contatoIndex.id != null && contato.id == contatoIndex.id) ||
                (contatoIndex.id == null && contato.$$hashKey == contatoIndex.$$hashKey);
        });
        this.entity.negocioscontatos.splice(indice, 1);
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
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.documento = response.data.documento;
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