export const MalotesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmMalotes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.malote) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.malote)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_malotes_not_found', $stateParams)) {
                                        $state.go('crm_malotes_not_found', $stateParams);
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
        .state('crm_malotes', {
            url: '/crm/malotes?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\MalotesListController',
            controllerAs: 'crm_mlts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_malotes_new', {
            parent: 'crm_malotes',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmMalotesFormNewController',
            controllerAs: 'crm_mlts_frm_nw_cntrllr'
        }).state('crm_malotes_show', {
            parent: 'crm_malotes',
            url: '/:malote',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmMalotesFormShowController',
            controllerAs: 'crm_mlts_frm_shw_cntrllr'
        }).state('crm_malotes_edit', {
            parent: 'crm_malotes',
            url: '/:malote/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\MalotesFormController',
            controllerAs: 'crm_mlts_frm_cntrllr'
        });
}]
