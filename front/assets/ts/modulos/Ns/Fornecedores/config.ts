export const FornecedoresRoutes = ['$stateProvider', '$stateRegistryProvider', ($stateProvider: angular.ui.IStateProvider, $stateRegistryProvider: any) => {

    let resolve = {
        'entity': ['NsFornecedores', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.fornecedor) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.fornecedor)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_fornecedores_not_found', $stateParams)) {
                                        $state.go('ns_fornecedores_not_found', $stateParams);
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
        'tiposatividadesfilterFilter': ['NsTiposatividades', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.tiposatividadesfilter) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.tiposatividadesfilter)
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
        // .state('crm_ns_fornecedores', {
        //     url: '/ns/fornecedores?q?tiposatividadesfilter?status?municipionome',
        //     resolve: resolveFilter,
        //     template: require('./index.html'),
        //     controller: 'Ns\FornecedoresListController',
        //     controllerAs: 'ns_frncdrs_lst_cntrllr',
        //     reloadOnSearch: false
        // })
        // .state('ns_fornecedores_new', {
        //     parent: 'crm_ns_fornecedores',
        //     url: '/new',
        //     resolve: resolve,
        //     template: require('./new.html'),
        //     controller: 'NsFornecedoresFormNewController',
        //     controllerAs: 'ns_frncdrs_frm_nw_cntrllr'
        // }).state('ns_fornecedores_show', {
        //     parent: 'crm_ns_fornecedores',
        //     url: '/:fornecedor',
        //     resolve: resolve,
        //     template: require('./show.html'),
        //     controller: 'NsFornecedoresFormShowController',
        //     controllerAs: 'ns_frncdrs_frm_shw_cntrllr'
        // }).state('ns_fornecedores_edit', {
        //     parent: 'crm_ns_fornecedores',
        //     url: '/:fornecedor/edit',
        //     resolve: resolve,
        //     template: require('./form.html'),
        //     controller: 'Ns\FornecedoresFormController',
        //     controllerAs: 'ns_frncdrs_frm_cntrllr'
        // });
        .state('ns_fornecedores', {
            url: '/crm/ns/fornecedores?q?tiposatividadesfilter?status',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\FornecedoresListController',
            controllerAs: 'ns_frncdrs_lst_cntrllr',
            reloadOnSearch: false,
            data: {
                chave: 'ns_fornecedores_index'
            }
        })
        .state('ns_fornecedores_new', {
            parent: 'ns_fornecedores',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsFornecedoresFormNewController',
            controllerAs: 'ns_frncdrs_frm_nw_cntrllr',
            data: {
                chave: 'ns_fornecedores_create'
            }
        }).state('ns_fornecedores_show', {
            parent: 'ns_fornecedores',
            url: '/:fornecedor',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsFornecedoresFormShowController',
            controllerAs: 'ns_frncdrs_frm_shw_cntrllr',
            data: {
                chave: 'ns_fornecedores_get'
            }
        }).state('ns_fornecedores_edit', {
            parent: 'ns_fornecedores',
            url: '/:fornecedor/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\FornecedoresFormController',
            controllerAs: 'ns_frncdrs_frm_cntrllr',
            data: {
                chave: 'ns_fornecedores_put'
            }
        });
}];

