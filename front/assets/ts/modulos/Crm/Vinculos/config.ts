export const VinculosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmVinculos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.vinculo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.vinculo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_vinculos_not_found', $stateParams)) {
                                        $state.go('crm_vinculos_not_found', $stateParams);
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
        .state('crm_vinculos', {
            url: '/crm/vinculos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\VinculosListController',
            controllerAs: 'crm_vncls_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_vinculos_new', {
            parent: 'crm_vinculos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmVinculosFormNewController',
            controllerAs: 'crm_vncls_frm_nw_cntrllr'
        }).state('crm_vinculos_show', {
            parent: 'crm_vinculos',
            url: '/:vinculo',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmVinculosFormShowController',
            controllerAs: 'crm_vncls_frm_shw_cntrllr'
        }).state('crm_vinculos_edit', {
            parent: 'crm_vinculos',
            url: '/:vinculo/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\VinculosFormController',
            controllerAs: 'crm_vncls_frm_cntrllr'
        });
}]