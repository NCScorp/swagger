export const TiposlogradourosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsTiposlogradouros', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tipologradouro) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.tipologradouro)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_tiposlogradouros_not_found', $stateParams)) {
                                        $state.go('ns_tiposlogradouros_not_found', $stateParams);
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
        .state('ns_tiposlogradouros', {
            url: '/ns/tiposlogradouros?q?tipologradouro?descricao',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\TiposlogradourosListController',
            controllerAs: 'ns_tpslgrdrs_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

