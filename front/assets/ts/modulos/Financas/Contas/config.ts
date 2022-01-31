
export const ContasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['FinancasContas', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.conta) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.conta)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('financas_contas_not_found', $stateParams)) {
                                        $state.go('financas_contas_not_found', $stateParams);
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
        .state('financas_contas', {
            url: '/financas/contas?q?bloqueado',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Financas\ContasListController',
            controllerAs: 'fnncs_cnts_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}]