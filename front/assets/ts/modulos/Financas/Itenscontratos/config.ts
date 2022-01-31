export const ItenscontratosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['FinancasItenscontratos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.itemcontrato) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.itemcontrato)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('financas_itenscontratos_not_found', $stateParams)) {
                                        $state.go('financas_itenscontratos_not_found', $stateParams);
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
        .state('financas_itenscontratos', {
            url: '/financas/itenscontratos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Financas\ItenscontratosListController',
            controllerAs: 'fnncs_tnscntrts_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

