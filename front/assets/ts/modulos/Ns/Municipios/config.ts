export const MunicipiosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsMunicipios', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.codigo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.codigo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_municipios_not_found', $stateParams)) {
                                        $state.go('ns_municipios_not_found', $stateParams);
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
        .state('ns_municipios', {
            url: '/ns/municipios?q?nome?uf',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\MunicipiosListController',
            controllerAs: 'ns_mncps_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_municipios_show', {
            parent: 'ns_municipios',
            url: '/:codigo',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsMunicipiosFormShowController',
            controllerAs: 'ns_mncps_frm_shw_cntrllr'
        });
}];