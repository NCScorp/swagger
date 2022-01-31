export const ListadavezregrasvaloresRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmListadavezregrasvalores', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.listadavezregravalor) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.listadavezregra, $stateParams.listadavezregravalor)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_listadavezregrasvalores_not_found', $stateParams)) {
                                        $state.go('crm_listadavezregrasvalores_not_found', $stateParams);
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
        .state('crm_listadavezregrasvalores', {
            url: '/{listadavezregra}/crm/listadavezregrasvalores?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ListadavezregrasvaloresListController',
            controllerAs: 'crm_lstdvzrgrsvlrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_listadavezregrasvalores_show', {
            url: '/{listadavezregra}/crm/listadavezregrasvalores/:listadavezregravalor',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmListadavezregrasvaloresFormShowController',
            controllerAs: 'crm_lstdvzrgrsvlrs_frm_shw_cntrllr'
        });
}]
    ;

