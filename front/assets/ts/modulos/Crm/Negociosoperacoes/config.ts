export const NegociosoperacoesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmNegociosoperacoes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.proposta_operacao) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.proposta_operacao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_negociosoperacoes_not_found', $stateParams)) {
                                        $state.go('crm_negociosoperacoes_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    return {};
                }
            }
        }]
    };
    let resolveFilter = {
    };
    $stateProvider
        .state('crm_negociosoperacoes', {
            url: '/crm/negociosoperacoes?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\NegociosoperacoesListController',
            controllerAs: 'crm_ngcsprcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_negociosoperacoes_show', {
            url: '/crm/negociosoperacoes/:proposta_operacao',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmNegociosoperacoesFormShowController',
            controllerAs: 'crm_ngcsprcs_frm_shw_cntrllr'
        });
}]
