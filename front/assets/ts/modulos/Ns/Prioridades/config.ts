import angular = require('angular');
export const PrioridadesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsPrioridades', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.prioridade) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.prioridade)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_prioridades_not_found', $stateParams)) {
                                        $state.go('ns_prioridades_not_found', $stateParams);
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
        .state('ns_prioridades', {
            url: '/ns/prioridades?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\PrioridadesListController',
            controllerAs: 'ns_prrdds_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_prioridades_new', {
            parent: 'ns_prioridades',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsPrioridadesFormNewController',
            controllerAs: 'ns_prrdds_frm_nw_cntrllr'
        }).state('ns_prioridades_show', {
            parent: 'ns_prioridades',
            url: '/:prioridade',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsPrioridadesFormShowController',
            controllerAs: 'ns_prrdds_frm_shw_cntrllr'
        }).state('ns_prioridades_edit', {
            parent: 'ns_prioridades',
            url: '/:prioridade/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\PrioridadesFormController',
            controllerAs: 'ns_prrdds_frm_cntrllr'
        });
}];


