export const AdvertenciasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsAdvertencias', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.advertencia) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.advertencia)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_advertencias_not_found', $stateParams)) {
                                        $state.go('ns_advertencias_not_found', $stateParams);
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
        'fornecedoresFilter': ['NsFornecedores', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.fornecedores) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.fornecedores)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            } else {
                return {};
            }
        }]
    };
    $stateProvider
        .state('ns_advertencias', {
            url: '/ns/advertencias?q?fornecedores',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\AdvertenciasListController',
            controllerAs: 'ns_dvrtncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_advertencias_show', {
            parent: 'ns_advertencias',
            url: '/:advertencia',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsAdvertenciasFormShowController',
            controllerAs: 'ns_dvrtncs_frm_shw_cntrllr'
        });
}];

