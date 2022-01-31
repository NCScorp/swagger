export const AtcsfollowupsanexosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcsfollowupsanexos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.negociofollowupanexo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negociofollowup, $stateParams.negociofollowupanexo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcsfollowupsanexos_not_found', $stateParams)) {
                                        $state.go('crm_atcsfollowupsanexos_not_found', $stateParams);
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
        .state('crm_atcsfollowupsanexos', {
            url: '/{negociofollowup}/crm/atcsfollowupsanexos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcsfollowupsanexosListController',
            controllerAs: 'crm_tcsfllwpsnxs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_atcsfollowupsanexos_new', {
            parent: 'crm_atcsfollowupsanexos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmAtcsfollowupsanexosFormNewController',
            controllerAs: 'crm_tcsfllwpsnxs_frm_nw_cntrllr'
        });
}];

