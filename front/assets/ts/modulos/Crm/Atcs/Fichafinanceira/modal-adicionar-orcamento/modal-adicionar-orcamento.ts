import angular = require('angular');
import { IOrcamento } from '../../../Orcamentos/classes/iorcamento';
import { IServico } from '../../../Buscarservicoseprodutos/classes/iservico';

export class CrmAtcsFichaFinanceiraAdicionarOrcamentoController{
    static $inject = [
        'toaster', 
        '$uibModalInstance', 
        'entity', 
        'constructors'
    ];

    /**
     * Formulário da tela
     */
    public form: any;
    /**
     * Objeto representando Serviço(Crm/composições) ou produto(Estoque/familias)
     */
    public servico: IServico;

    constructor(
        public toaster: any, 
        public $uibModalInstance: any,
        public entity: IOrcamento,
        public constructors: any
    ) {}

    /**
     * Fecha tela
     */
    close(){
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Fecha tela, retornando interface de orçamento
     */
    submit(){
        if (this.form.$valid) {
            this.entity.composicao = null;
            this.entity.familia = null;
            this.entity.composicao = this.servico.composicao;
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    /**
     * Evento disparado quando o lookup de serviços/produtos mudar
     * @param ev 
     */
    onServicoChange(){
        this.entity.descricaomanual = this.servico != null && this.servico.servicocoringa;
    }
}

export class CrmAtcsFichaFinanceiraAdicionarOrcamentoService{
    static $inject = [ '$uibModal'];

    constructor(
        public $uibModal: any
    ) {}

    /**
     * Abre modal de adicionar serviço ao orçamento
     */
    open(parameters: any, subentity: IOrcamento) {
        return this.$uibModal.open({
            template: require('./modal-adicionar-orcamento.html'),
            controller: 'CrmAtcsFichaFinanceiraAdicionarOrcamentoController',
            controllerAs: 'crm_atcficha_add_ocmt_ctrl',
            windowClass: '',
            resolve: {
                entity: () => {
                    return angular.copy(subentity);
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}
