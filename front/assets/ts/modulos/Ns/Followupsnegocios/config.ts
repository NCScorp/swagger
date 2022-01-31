export const FollowupsnegociosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsFollowupsnegocios', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.followup) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.proposta, $stateParams.followup)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_followupsnegocios_not_found', $stateParams)) {
                                        $state.go('ns_followupsnegocios_not_found', $stateParams);
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
        .state('ns_followupsnegocios', {
            url: '/{proposta}/ns/followupsnegocios?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\FollowupsnegociosListController',
            controllerAs: 'ns_fllwpsngcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_followupsnegocios_new', {
            url: '/{proposta}/ns/followupsnegocios/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsFollowupsnegociosFormNewController',
            controllerAs: 'ns_fllwpsngcs_frm_nw_cntrllr'
        });
}];

