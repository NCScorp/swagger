
export const VendedoresRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsVendedores', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.vendedor_id) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.vendedor_id)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_vendedores_not_found', $stateParams)) {
                                        $state.go('ns_vendedores_not_found', $stateParams);
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
        .state('ns_vendedores', {
            url: '/ns/vendedores?q?vendedor_bloqueado?vendedoremail',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\VendedoresListController',
            controllerAs: 'ns_vnddrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_vendedores_show', {
            parent: 'ns_vendedores',
            url: '/:vendedor_id',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsVendedoresFormShowController',
            controllerAs: 'ns_vnddrs_frm_shw_cntrllr'
        });
}]