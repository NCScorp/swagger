export const FornecedorespedidoselecaoRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmFornecedorespedidoselecao', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.fornecedor) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.fornecedor)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_fornecedorespedidoselecao_not_found', $stateParams)) {
                                        $state.go('crm_fornecedorespedidoselecao_not_found', $stateParams);
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
        .state('crm_fornecedorespedidoselecao', {
            url: '/crm/fornecedorespedidoselecao?q?municipioibge',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\FornecedorespedidoselecaoListController',
            controllerAs: 'crm_frncdrspddslc_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

