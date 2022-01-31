export const FornecedoressuspensosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsFornecedoressuspensos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.fornecedorsuspenso) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.fornecedorsuspenso)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_fornecedoressuspensos_not_found', $stateParams)) {
                                        $state.go('ns_fornecedoressuspensos_not_found', $stateParams);
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
        'fornecedoresfilterFilter': ['NsFornecedores', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.fornecedoresfilter) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.fornecedoresfilter)
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
        .state('ns_fornecedoressuspensos', {
            url: '/ns/fornecedoressuspensos?q?fornecedoresfilter',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\FornecedoressuspensosListController',
            controllerAs: 'ns_frncdrssspnss_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_fornecedoressuspensos_show', {
            parent: 'ns_fornecedoressuspensos',
            url: '/:fornecedorsuspenso',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsFornecedoressuspensosFormShowController',
            controllerAs: 'ns_frncdrssspnss_frm_shw_cntrllr'
        });
}]