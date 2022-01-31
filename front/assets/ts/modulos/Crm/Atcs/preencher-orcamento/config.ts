import { NsjAuth } from "@nsj/core";
import { HashService } from "@nsj/core/authentication/services/hash/hash.service";

export const atcpreencherorcamentoRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcs', '$stateParams', '$state', 'toaster', (CrmAtcs, $stateParams, $state, toaster) => {
            return new Promise((resolve: any, reject: any) => {
                CrmAtcs.get($stateParams.atc, true)
                    .then((data: any) => {
                        resolve(data);
                    })
                    .catch((error: any) => {
                        if (error.status === 404) {
                            if ($state.href('crm_atcs_not_found', $stateParams)) {
                                $state.go('crm_atcs_not_found', $stateParams);
                            } else {
                                $state.go('not_found', $stateParams);
                            }
                        } else {
                            toaster.pop({
                                type: 'error',
                                title: 'Não foi possível buscar dados do atendimento, você será deslogado em 5 segundos...'
                            });
                            setTimeout(() => {
                                NsjAuth.authService.logout();
                            }, 5000);
                        }
                    });
            });    
        }],
        'parametros': ['toaster', (toaster) => {
            // Busco fornecedorID dos dados do Hash
            let fornecedorID = '';

            if (HashService.dados) {
                const arrFornecedorID = HashService.dados.entidades['ns/fornecedores'];

                if (Array.isArray(arrFornecedorID) && arrFornecedorID.length > 0) {
                    fornecedorID = arrFornecedorID[0];
                }
            }

            if (fornecedorID == '') {
                toaster.pop({
                    type: 'error',
                    title: 'Não foi possível buscar dados do atendimento, você será deslogado em 5 segundos...'
                });
                setTimeout(() => {
                    NsjAuth.authService.logout();
                }, 5000);
            }
            return {
                fornecedor: fornecedorID
            }
        }]
    };
    
    $stateProvider
        .state('crm_atcs_preencher_orcamento', {
            url: '/crm/atcs/:atc/preencher-orcamento?hash',
            resolve: resolve,
            template: require('./preencher-orcamento.html'),
            controller: 'CrmAtcsPreencherOrcamentoFornecedorController',
            controllerAs: 'crm_atcs_prcr_orc_cntrllr'
        });
}]