import angular = require('angular');

export class CrmPropostasitensfuncoesFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.propostaitemfuncao ? 'update' : 'insert';
    }

    validarCampos(): boolean {
        let valido: boolean = true;

        if (this.entity.quantidade > 2147483648) {
            valido = false;
            this.toaster.pop({
                type: 'error',
                title: 'A quantidade não pode ser maior 2147483648.'
            });
        } 
        
        return valido;
    }

    /**
     * Verifica se a proposta item função adicionada permite informar descrição
     */
    verificaSeHaNomeManual(funcao: any){

        //Baseado na função recebida, verifico se a proposta item função permite informar a descrição
        return funcao.funcaocoringa ? true : false;

    }

    submit() {

        // Se estiver inserindo nova proposta item função, preciso consultar a sua função para saber se permite informar a descrição. Na edição a entity já tem essa propriedade, que é imutável
        if(this.action == 'insert'){
            this.entity.nomefuncaoalterado = this.verificaSeHaNomeManual(this.entity.funcao);

            // Se a proposta item função não permitir adicionar o nome manualmente, uso como seu nome a descrição da função
            if(!this.entity.nomefuncaoalterado){
                this.entity.nome = this.entity.funcao.descricao;
            }

        }

        this.submitted = true;

        if (this.form.$valid) {
            this.form.$valid = this.validarCampos();
            if (this.form.$valid) {
                this.$uibModalInstance.close(this.entity);
            }    
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmPropostasitensfuncoesFormService {
    static $inject = ['CrmPropostasitensfuncoes', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./../../Crm/Propostasitensfuncoes/modal.html'),
                controller: 'CrmPropostasitensfuncoesFormModalController',
                controllerAs: 'crm_prpststnsfncs_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.propostaitemfuncao && !subentity.propostaitemfuncao) {
                           let entity = this.entityService.get(parameters.propostaitem, parameters.propostaitemfuncao, parameters.negocio, parameters.proposta);
                           return entity;
                        } else {
                           subentity['negocio'] = parameters.negocio;
                           subentity['proposta'] = parameters.proposta;
                           return angular.copy(subentity);
                        }
                    },
                    constructors: () => {
                        return parameters;
                    }
                }
            });
    }

}

