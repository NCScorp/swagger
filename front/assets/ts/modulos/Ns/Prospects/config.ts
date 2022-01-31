export const ProspectsRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsProspects', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.prospect) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.prospect)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_prospects_not_found', $stateParams)) {
                                        $state.go('ns_prospects_not_found', $stateParams);
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
        .state('ns_prospects', {
            url: '/ns/prospects?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\ProspectsListController',
            controllerAs: 'ns_prspcts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_prospects_new', {
            url: '/ns/prospects/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsProspectsFormNewController',
            controllerAs: 'ns_prspcts_frm_nw_cntrllr'
        });
}];

