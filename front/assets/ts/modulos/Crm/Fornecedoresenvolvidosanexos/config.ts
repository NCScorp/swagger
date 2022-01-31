export const FornecedoresenvolvidosanexosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmFornecedoresenvolvidosanexos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.fornecedorenvolvidoanexo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.fornecedorenvolvido, $stateParams.fornecedorenvolvidoanexo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_fornecedoresenvolvidosanexos_not_found', $stateParams)) {
                                        $state.go('crm_fornecedoresenvolvidosanexos_not_found', $stateParams);
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
        .state('crm_fornecedoresenvolvidosanexos', {
            url: '/{fornecedorenvolvido}/crm/fornecedoresenvolvidosanexos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\FornecedoresenvolvidosanexosListController',
            controllerAs: 'crm_frncdrsnvlvdsnxs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_fornecedoresenvolvidosanexos_new', {
            parent: 'crm_fornecedoresenvolvidosanexos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmFornecedoresenvolvidosanexosFormNewController',
            controllerAs: 'crm_frncdrsnvlvdsnxs_frm_nw_cntrllr'
        });
}];

