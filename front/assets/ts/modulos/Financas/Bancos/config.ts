 export const BancosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['FinancasBancos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.banco) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.banco)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('financas_bancos_not_found', $stateParams)) {
                                        $state.go('financas_bancos_not_found', $stateParams);
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
        .state('financas_bancos', {
            url: '/financas/bancos?q?numero?nome',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Financas\BancosListController',
            controllerAs: 'fnncs_bncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('financas_bancos_show', {
            parent: 'financas_bancos',
            url: '/:banco',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'FinancasBancosFormShowController',
            controllerAs: 'fnncs_bncs_frm_shw_cntrllr'
        });
}]