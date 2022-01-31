export const ListadavezregrasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmListadavezregras', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.listadavezregra) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.listadavezregra)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_listadavezregras_not_found', $stateParams)) {
                                        $state.go('crm_listadavezregras_not_found', $stateParams);
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
        .state('crm_listadavezregras', {
            url: '/crm/listadavezregras?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ListadavezregrasListController',
            controllerAs: 'crm_lstdvzrgrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_listadavezregras_show', {
            url: '/crm/listadavezregras/:listadavezregra',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmListadavezregrasFormShowController',
            controllerAs: 'crm_lstdvzrgrs_frm_shw_cntrllr'
        });
}];

