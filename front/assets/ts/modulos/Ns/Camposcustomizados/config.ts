export const NsCamposcustomizadosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsCamposcustomizados', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.campocustomizado) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.campocustomizado)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_camposcustomizados_not_found', $stateParams)) {
                                        $state.go('ns_camposcustomizados_not_found', $stateParams);
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
        .state('ns_camposcustomizados', {
            url: '/ns/camposcustomizados?q?crmnegocio?crmnegociosvisualizacao?crmnegocioexibenalistagem?secao',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\CamposcustomizadosListController',
            controllerAs: 'ns_cmpscstmzds_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_camposcustomizados_new', {
            parent: 'ns_camposcustomizados',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsCamposcustomizadosFormNewController',
            controllerAs: 'ns_cmpscstmzds_frm_nw_cntrllr'
        }).state('ns_camposcustomizados_edit', {
            parent: 'ns_camposcustomizados',
            url: '/:campocustomizado/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\CamposcustomizadosFormController',
            controllerAs: 'ns_cmpscstmzds_frm_cntrllr'
        });
}];

