export const atcsAreasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcsareas', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.negocioarea) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negocioarea)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcsareas_not_found', $stateParams)) {
                                        $state.go('crm_atcsareas_not_found', $stateParams);
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
        .state('crm_atcsareas', {
            url: '/crm/atcsareas?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcsareasListController',
            controllerAs: 'crm_tcsrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_atcsareas_new', {
            parent: 'crm_atcsareas',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmAtcsareasFormNewController',
            controllerAs: 'crm_tcsrs_frm_nw_cntrllr'
        }).state('crm_atcsareas_show', {
            parent: 'crm_atcsareas',
            url: '/:negocioarea',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmAtcsareasFormShowController',
            controllerAs: 'crm_tcsrs_frm_shw_cntrllr'
        }).state('crm_atcsareas_edit', {
            parent: 'crm_atcsareas',
            url: '/:negocioarea/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\AtcsareasFormController',
            controllerAs: 'crm_tcsrs_frm_cntrllr'
        });
}];

