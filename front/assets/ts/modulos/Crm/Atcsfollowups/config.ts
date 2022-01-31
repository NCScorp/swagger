export const AtcsfollowupsRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcsfollowups', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.negociofollowup) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negocio, $stateParams.negociofollowup)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcsfollowups_not_found', $stateParams)) {
                                        $state.go('crm_atcsfollowups_not_found', $stateParams);
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
        .state('crm_atcsfollowups', {
            url: '/{negocio}/crm/atcsfollowups?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcsfollowupsListController',
            controllerAs: 'crm_tcsfllwps_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_atcsfollowups_new', {
            parent: 'crm_atcsfollowups',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmAtcsfollowupsFormNewController',
            controllerAs: 'crm_tcsfllwps_frm_nw_cntrllr'
        }).state('crm_atcsfollowups_show', {
            parent: 'crm_atcsfollowups',
            url: '/:negociofollowup',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmAtcsfollowupsFormShowController',
            controllerAs: 'crm_tcsfllwps_frm_shw_cntrllr'
        });
}];

