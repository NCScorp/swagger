export const ClientesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsClientes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.cliente) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.cliente)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_clientes_not_found', $stateParams)) {
                                        $state.go('ns_clientes_not_found', $stateParams);
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
        .state('ns_clientes', {
            url: '/crm/ns/clientes?q?tipo?tiposatividadesfilter?cnpj',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\ClientesListController',
            controllerAs: 'ns_clnts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_clientes_new', {
            parent: 'ns_clientes',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsClientesFormNewController',
            controllerAs: 'ns_clnts_frm_nw_cntrllr'
        }).state('ns_clientes_show', {
            parent: 'ns_clientes',
            url: '/:cliente',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsClientesFormShowController',
            controllerAs: 'ns_clnts_frm_shw_cntrllr'
        }).state('ns_clientes_edit', {
            parent: 'ns_clientes',
            url: '/:cliente/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\ClientesFormController',
            controllerAs: 'ns_clnts_frm_cntrllr'
        });
}]