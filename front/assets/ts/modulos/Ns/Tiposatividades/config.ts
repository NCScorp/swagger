export const TiposatividadesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsTiposatividades', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tipoatividade) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.tipoatividade)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_tiposatividades_not_found', $stateParams)) {
                                        $state.go('ns_tiposatividades_not_found', $stateParams);
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
        .state('ns_tiposatividades', {
            url: '/ns/tiposatividades?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\TiposatividadesListController',
            controllerAs: 'ns_tpstvdds_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_tiposatividades_new', {
            parent: 'ns_tiposatividades',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsTiposatividadesFormNewController',
            controllerAs: 'ns_tpstvdds_frm_nw_cntrllr'
        }).state('ns_tiposatividades_show', {
            parent: 'ns_tiposatividades',
            url: '/:tipoatividade',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsTiposatividadesFormShowController',
            controllerAs: 'ns_tpstvdds_frm_shw_cntrllr'
        }).state('ns_tiposatividades_edit', {
            parent: 'ns_tiposatividades',
            url: '/:tipoatividade/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\TiposatividadesFormController',
            controllerAs: 'ns_tpstvdds_frm_cntrllr'
        });
}]