export const UnidadesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['EstoqueUnidades', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.unidade) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.unidade)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('estoque_unidades_not_found', $stateParams)) {
                                        $state.go('estoque_unidades_not_found', $stateParams);
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
        .state('estoque_unidades', {
            url: '/estoque/unidades?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Estoque\UnidadesListController',
            controllerAs: 'stq_ndds_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('estoque_unidades_new', {
            parent: 'estoque_unidades',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'EstoqueUnidadesFormNewController',
            controllerAs: 'stq_ndds_frm_nw_cntrllr'
        }).state('estoque_unidades_show', {
            parent: 'estoque_unidades',
            url: '/:unidade',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'EstoqueUnidadesFormShowController',
            controllerAs: 'stq_ndds_frm_shw_cntrllr'
        }).state('estoque_unidades_edit', {
            parent: 'estoque_unidades',
            url: '/:unidade/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Estoque\UnidadesFormController',
            controllerAs: 'stq_ndds_frm_cntrllr'
        });
}]