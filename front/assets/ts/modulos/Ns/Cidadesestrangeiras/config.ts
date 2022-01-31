export const CidadesestrangeirasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsCidadesestrangeiras', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.cidadeestrangeira) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.cidadeestrangeira)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_cidadesestrangeiras_not_found', $stateParams)) {
                                        $state.go('ns_cidadesestrangeiras_not_found', $stateParams);
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
        .state('ns_cidadesestrangeiras', {
            url: '/ns/cidadesestrangeiras?q?nome',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\CidadesestrangeirasListController',
            controllerAs: 'ns_cddsstrngrs_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

