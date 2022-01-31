export const ContratosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['FinancasContratos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.contrato) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.contrato)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('financas_contratos_not_found', $stateParams)) {
                                        $state.go('financas_contratos_not_found', $stateParams);
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
        .state('financas_contratos', {
            url: '/financas/contratos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Financas\ContratosListController',
            controllerAs: 'fnncs_cntrts_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

