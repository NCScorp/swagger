export const PaisesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsPaises', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.pais) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.pais)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_paises_not_found', $stateParams)) {
                                        $state.go('ns_paises_not_found', $stateParams);
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
        .state('ns_paises', {
            url: '/ns/paises?q?nome',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\PaisesListController',
            controllerAs: 'ns_pss_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_paises_show', {
            parent: 'ns_paises',
            url: '/:pais',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsPaisesFormShowController',
            controllerAs: 'ns_pss_frm_shw_cntrllr'
        });
}]