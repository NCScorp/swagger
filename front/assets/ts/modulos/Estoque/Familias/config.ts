export const FamiliasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['EstoqueFamilias', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.familia) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.familia)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('estoque_familias_not_found', $stateParams)) {
                                        $state.go('estoque_familias_not_found', $stateParams);
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
        .state('estoque_familias', {
            url: '/estoque/familias?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Estoque\FamiliasListController',
            controllerAs: 'stq_fmls_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}]