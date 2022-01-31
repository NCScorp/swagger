export const ConfiguracoestaxasadministrativasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmConfiguracoestaxasadministrativas', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.configuracaotaxaadm) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.configuracaotaxaadm)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_configuracoestaxasadministrativas_not_found', $stateParams)) {
                                        $state.go('crm_configuracoestaxasadministrativas_not_found', $stateParams);
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
        .state('crm_configuracoestaxasadministrativas', {
            url: '/crm/configuracoestaxasadministrativas?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'CrmConfiguracoestaxasadministrativasListController',
            controllerAs: 'crm_cfgtxdm_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_configuracoestaxasadministrativas_new', {
            parent: 'crm_configuracoestaxasadministrativas',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmConfiguracoestaxasadministrativasFormNewController',
            controllerAs: 'crm_cfgtxdm_frm_nw_cntrllr'
        }).state('crm_configuracoestaxasadministrativas_show', {
            parent: 'crm_configuracoestaxasadministrativas',
            url: '/:configuracaotaxaadm',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmConfiguracoestaxasadministrativasFormShowController',
            controllerAs: 'crm_cfgtxdm_frm_shw_cntrllr'
        }).state('crm_configuracoestaxasadministrativas_edit', {
            parent: 'crm_configuracoestaxasadministrativas',
            url: '/:configuracaotaxaadm/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'CrmConfiguracoestaxasadministrativasFormController',
            controllerAs: 'crm_cfgtxdm_frm_cntrllr'
        });
}];

