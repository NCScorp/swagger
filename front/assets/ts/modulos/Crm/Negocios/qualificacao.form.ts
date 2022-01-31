import angular = require('angular');
import { CrmNegocios } from './factory';
import { IListadavezvendedoritem } from '../Listadavezvendedores/classes';
export class CrmNegociosQualificacaoController {

    static $inject = [
        '$scope', 
        'toaster', 
        'CrmNegocios', 
        'utilService'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    /**
     * Faz bind com checkbox utilizado para definir se utiliza lista da vez ou não.
     */
    private utilizarListDaVez: boolean = false;
    /**
     * Representa o retorno da lista da vez
     */
    private listadavezvendedoritem: IListadavezvendedoritem = null;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any, 
        public entityService: CrmNegocios, 
        public utilService: any
    ) {}

    /**
     * Função chamada ao iniciar a tela
     */
    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        this.utilizarListDaVez = true;
        this.utilizarOuNaoListaDaVez();
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
     * Atualiza scopo da página
     */
    private reloadScope(){
        this.$scope.$applyAsync();
    }

    /**
     * Função chamada quando o checkbox de lista da vez for clicado
     */
    private onListaDaVezCheckboxClick() {
        this.utilizarListDaVez = !this.utilizarListDaVez;

        this.utilizarOuNaoListaDaVez();
    }

    /**
     * Seleciona o vendedor pela lista da vez ou deixa selecionar livremente, de acordo com o atributo "utilizarListDaVez"
     */
    private async utilizarOuNaoListaDaVez(){
        if (this.utilizarListDaVez) {
            if (this.listadavezvendedoritem == null) {
                this.busy = true;
            }
            try {
                const listadavezvendedoritem = await this.getListaDaVez();
                this.entity.vendedor = listadavezvendedoritem.vendedor;
                
                if (listadavezvendedoritem.listadavezvendedoritem != null) {
                    this.entity.listadavezvendedor = listadavezvendedoritem.listadavezvendedor.listadavezvendedor;
                    this.entity.listadavezvendedoritem = listadavezvendedoritem.listadavezvendedoritem;
                }
            } catch (error) {
                this.tratarRequestErroMensagem(error);
                this.utilizarListDaVez = false;
            } finally {
                this.busy = false;
                this.reloadScope();
            }
        } else {
            this.entity.vendedor = null;
            this.entity.listadavezvendedor = null;
            this.entity.listadavezvendedoritem = null;
        }
    }

    private onLookupVendedorChange(){
        // Se está utilizando lista da vez
        if (this.utilizarListDaVez) {
            if (!this.entity.vendedor || this.entity.vendedor.vendedor_id != this.listadavezvendedoritem.vendedor.vendedor_id) {
                this.entity.listadavezvendedor = null;
                this.entity.listadavezvendedoritem = null;
                this.utilizarListDaVez = false;
            }
        }
    }

    /**
     * Retorna o listadavezvendedoritem referente a lista da vez
     */
    private getListaDaVez(): Promise<IListadavezvendedoritem> {
        return new Promise(async (resolve, reject) => {
            try {
                // Só faço requisição para buscar o registro se já não tiver buscado
                if (this.listadavezvendedoritem == null) {
                    this.listadavezvendedoritem = await this.entityService.getListaDaVez(this.entity.documento);
                }

                resolve(this.listadavezvendedoritem);
            } catch (error) {
                reject(error)
            }
        });
    }

    /**
     * Apresenta mensagem de erro de acordo com o retorno
     * @param response 
     */
    private tratarRequestErroMensagem(response) {
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
                    title: 'Erro ao buscar lista da vez.'
                });
        }
    }
}