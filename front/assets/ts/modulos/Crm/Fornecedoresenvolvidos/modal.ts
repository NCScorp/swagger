import angular = require('angular');
import { CrmFornecedoresenvolvidosFormModalController as ParentController } from './../../Crm/Fornecedoresenvolvidos/modal';
import { CrmFornecedoresenvolvidosFormService as ParentService } from './../../Crm/Fornecedoresenvolvidos/modal';
import { CrmFichafinanceiraFormService } from '../Atcs/ficha-financeira-modal';
import { WebConfiguracoes } from './../../Web/Configuracoes/factory';

export class CrmFornecedoresenvolvidosFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entity', 'constructors', 'CrmFichafinanceiraFormService', 'configuracoes', '$rootScope'];

    public action: string;
    public form: any;
    public submitted: boolean = false;

    constructor(
        public toaster: any, 
        public $uibModalInstance: any, 
        public entity: any, 
        public constructors: any,
        public CrmFichafinanceiraFormService: CrmFichafinanceiraFormService,
        public configuracoes: any,
        public $rootScope: angular.IRootScopeService,
    ) {
        this.action = entity.fornecedorenvolvido ? 'update' : 'insert';

        if (configuracoes.length > 0) {
            this.entity.acionamentorespostaprazo =   Number(configuracoes[0].valor);
        }
    }

    submit() {
        this.submitted = true;
        if (this.form.$valid) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
        this.$rootScope.$broadcast('ativar_botao_acionar_prestador');
    }
    close() {
        this.$rootScope.$broadcast('ativar_botao_acionar_prestador');
        this.$uibModalInstance.dismiss('fechar');
    }
}

export class CrmFornecedoresenvolvidosFormService {
    static $inject = ['CrmFornecedoresenvolvidos', '$uibModal', 'WebConfiguracoes', 'nsjRouting', '$http'];

    constructor(
        public entityService: any, 
        public $uibModal: any,
        public WebConfiguracoes: WebConfiguracoes,
        public nsjRouting: any,
        public $http: any
    ) {
    }

    open(parameters: any, subentity: any) {
            return this.$uibModal.open({
                // template: require('./modal.html'),
                template: require('./../../Crm/Fornecedoresenvolvidos/modal.html'),
                controller: 'CrmFornecedoresenvolvidosFormModalController',
                controllerAs: 'crm_frncdrsnvlvds_frm_dflt_cntrllr',
                windowClass: '',
                backdrop: 'static',
                resolve: {
                entity: () => {
                        if (parameters.identifier) {
                           let entity = this.entityService.get(parameters.atc, parameters.identifier);
                           return entity;
                        } else {
                           return angular.copy(subentity);
                        }
                    },
                    constructors: () => {
                        return parameters;
                    },
                    configuracoes: () => {
                        let filters: any = {
                            chave: 'PRESTADORPRAZOACIONAMENTORESPOSTA',
                            sistema: 'CRMWEB'
                        }
                        
                        let promise = new Promise((resolve, reject) => {
                            this.$http({
                                method: 'GET',
                                url: this.nsjRouting.generate('web_configuracoes_index', filters, false)
                            }).then((response: any) => {
                                resolve(response.data);
                            }).catch((erro: any) => {
                                console.log('Erro', erro);
                                resolve([]);
                            });
                        });

                        return promise;
                    }
                }
            });
    }
}


