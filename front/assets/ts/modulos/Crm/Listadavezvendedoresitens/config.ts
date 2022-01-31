export const ListadavezvendedoresitensRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmListadavezvendedoresitens', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.listadavezvendedoritem) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.listadavezvendedor, $stateParams.listadavezvendedoritem)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_listadavezvendedoresitens_not_found', $stateParams)) {
                                        $state.go('crm_listadavezvendedoresitens_not_found', $stateParams);
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
        .state('crm_listadavezvendedoresitens', {
            url: '/{listadavezvendedor}/crm/listadavezvendedoresitens?q?posicao',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ListadavezvendedoresitensListController',
            controllerAs: 'crm_lstdvzvnddrstns_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_listadavezvendedoresitens_new', {
            url: '/{listadavezvendedor}/crm/listadavezvendedoresitens/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmListadavezvendedoresitensFormNewController',
            controllerAs: 'crm_lstdvzvnddrstns_frm_nw_cntrllr'
        }).state('crm_listadavezvendedoresitens_show', {
            url: '/{listadavezvendedor}/crm/listadavezvendedoresitens/:listadavezvendedoritem',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmListadavezvendedoresitensFormShowController',
            controllerAs: 'crm_lstdvzvnddrstns_frm_shw_cntrllr'
        }).state('crm_listadavezvendedoresitens_edit', {
            url: '/{listadavezvendedor}/crm/listadavezvendedoresitens/:listadavezvendedoritem/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\ListadavezvendedoresitensFormController',
            controllerAs: 'crm_lstdvzvnddrstns_frm_cntrllr'
        });
}]