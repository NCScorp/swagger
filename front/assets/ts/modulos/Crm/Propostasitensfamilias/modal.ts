import angular = require('angular');

export class CrmPropostasitensfamiliasFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors'];

    public action: string;
        public form: any;
    public submitted: boolean = false;
        constructor(public toaster: any, public $uibModalInstance: any, public entity: any, public constructors: any) {
        this.action = entity.propostaitemfamilia ? 'update' : 'insert';
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
     * Verifica se a proposta item família adicionada permite informar descrição
     */
     verificaSeHaNomeManual(familia: any){

        //Baseado na família recebida, verifico se a proposta item família permite informar a descrição
        return familia.familiacoringa ? true : false;

    }

    submit() {

        // Se estiver inserindo nova proposta item família, preciso consultar a sua família para saber se permite informar a descrição. Na edição a entity já tem essa propriedade, que é imutável
        if(this.action == 'insert'){
            this.entity.nomefamiliaalterado = this.verificaSeHaNomeManual(this.entity.familia);

            // Se a proposta item família não permitir adicionar o nome manualmente, uso como seu nome a descrição da família
            if(!this.entity.nomefamiliaalterado){
                this.entity.nome = this.entity.familia.descricao;
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
                title: 'Alguns campos do formulário apresentam erros'
            });
        }
    }
    close() {
    this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmPropostasitensfamiliasFormService {
    static $inject = ['CrmPropostasitensfamilias', '$uibModal'];

    constructor(public entityService: any, public $uibModal: any) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                template: require('./../../Crm/Propostasitensfamilias/modal.html'),
                controller: 'CrmPropostasitensfamiliasFormModalController',
                controllerAs: 'crm_prpststnsfmls_frm_dflt_cntrllr',
                windowClass: '',
                resolve: {
                entity: () => {
                        if (parameters.propostaitemfamilia && !subentity.propostaitemfamilia) {
                           let entity = this.entityService.get(parameters.propostaitem, parameters.propostaitemfamilia, parameters.negocio, parameters.proposta);
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
