export const ListadavezvendedoresRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmListadavezvendedores', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.listadavezvendedor) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.listadavezvendedor)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_listadavezvendedores_not_found', $stateParams)) {
                                        $state.go('crm_listadavezvendedores_not_found', $stateParams);
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
        .state('crm_listadavezvendedores', {
            url: '/crm/listadavezvendedores?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ListadavezvendedoresListController',
            controllerAs: 'crm_lstdvzvnddrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_listadavezvendedores_new', {
            url: '/crm/listadavezvendedores/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmListadavezvendedoresFormNewController',
            controllerAs: 'crm_lstdvzvnddrs_frm_nw_cntrllr'
        }).state('crm_listadavezvendedores_show', {
            url: '/crm/listadavezvendedores/:listadavezvendedor',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmListadavezvendedoresFormShowController',
            controllerAs: 'crm_lstdvzvnddrs_frm_shw_cntrllr'
        }).state('crm_listadavezvendedores_edit', {
            url: '/crm/listadavezvendedores/:listadavezvendedor/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\ListadavezvendedoresFormController',
            controllerAs: 'crm_lstdvzvnddrs_frm_cntrllr'
        });
}];

