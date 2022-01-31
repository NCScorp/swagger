export const midiasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmMidias', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.midia) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.midia)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_midias_not_found', $stateParams)) {
                                        $state.go('crm_midias_not_found', $stateParams);
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
        .state('crm_midias', {
            url: '/crm/midias?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\MidiasListController',
            controllerAs: 'crm_mds_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_midias_new', {
            parent: 'crm_midias',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmMidiasFormNewController',
            controllerAs: 'crm_mds_frm_nw_cntrllr'
        }).state('crm_midias_show', {
            parent: 'crm_midias',
            url: '/:midia',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmMidiasFormShowController',
            controllerAs: 'crm_mds_frm_shw_cntrllr'
        }).state('crm_midias_edit', {
            parent: 'crm_midias',
            url: '/:midia/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\MidiasFormController',
            controllerAs: 'crm_mds_frm_cntrllr'
        });
}]