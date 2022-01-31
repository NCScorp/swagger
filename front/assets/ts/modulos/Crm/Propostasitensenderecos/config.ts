export const PropostasitensenderecosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmPropostasitensenderecos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.propostaitemendereco) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.propostaitem, $stateParams.propostaitemendereco)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_propostasitensenderecos_not_found', $stateParams)) {
                                        $state.go('crm_propostasitensenderecos_not_found', $stateParams);
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
        .state('crm_propostasitensenderecos', {
            url: '/{propostaitem}/crm/propostasitensenderecos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\PropostasitensenderecosListController',
            controllerAs: 'crm_prpststnsndrcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_propostasitensenderecos_new', {
            parent: 'crm_propostasitensenderecos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmPropostasitensenderecosFormNewController',
            controllerAs: 'crm_prpststnsndrcs_frm_nw_cntrllr'
        }).state('crm_propostasitensenderecos_edit', {
            parent: 'crm_propostasitensenderecos',
            url: '/:propostaitemendereco/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\PropostasitensenderecosFormController',
            controllerAs: 'crm_prpststnsndrcs_frm_cntrllr'
        });
}]