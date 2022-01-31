export const TiposacionamentosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmTiposacionamentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tiposacionamento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.tiposacionamento)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_tiposacionamentos_not_found', $stateParams)) {
                                        $state.go('crm_tiposacionamentos_not_found', $stateParams);
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
        .state('crm_tiposacionamentos', {
            url: '/crm/tiposacionamentos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\TiposacionamentosListController',
            controllerAs: 'crm_tpscnmnts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_tiposacionamentos_new', {
            parent: 'crm_tiposacionamentos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmTiposacionamentosFormNewController',
            controllerAs: 'crm_tpscnmnts_frm_nw_cntrllr'
        }).state('crm_tiposacionamentos_show', {
            parent: 'crm_tiposacionamentos',
            url: '/:tiposacionamento',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmTiposacionamentosFormShowController',
            controllerAs: 'crm_tpscnmnts_frm_shw_cntrllr'
        }).state('crm_tiposacionamentos_edit', {
            parent: 'crm_tiposacionamentos',
            url: '/:tiposacionamento/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\TiposacionamentosFormController',
            controllerAs: 'crm_tpscnmnts_frm_cntrllr'
        });
}]