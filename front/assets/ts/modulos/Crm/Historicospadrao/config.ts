export const HistoricospadraoRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmHistoricospadrao', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.historicopadrao) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.historicopadrao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_historicospadrao_not_found', $stateParams)) {
                                        $state.go('crm_historicospadrao_not_found', $stateParams);
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
        .state('crm_historicospadrao', {
            url: '/crm/historicospadrao?q?tipo',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\HistoricospadraoListController',
            controllerAs: 'crm_hstrcspdr_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_historicospadrao_new', {
            parent: 'crm_historicospadrao',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmHistoricospadraoFormNewController',
            controllerAs: 'crm_hstrcspdr_frm_nw_cntrllr'
        }).state('crm_historicospadrao_show', {
            parent: 'crm_historicospadrao',
            url: '/:historicopadrao',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmHistoricospadraoFormShowController',
            controllerAs: 'crm_hstrcspdr_frm_shw_cntrllr'
        }).state('crm_historicospadrao_edit', {
            parent: 'crm_historicospadrao',
            url: '/:historicopadrao/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\HistoricospadraoFormController',
            controllerAs: 'crm_hstrcspdr_frm_cntrllr'
        });
}];

