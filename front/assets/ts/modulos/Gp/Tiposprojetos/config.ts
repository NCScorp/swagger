export const TiposprojetosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['GpTiposprojetos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tipoprojeto) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.tipoprojeto)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('gp_tiposprojetos_not_found', $stateParams)) {
                                        $state.go('gp_tiposprojetos_not_found', $stateParams);
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
        .state('gp_tiposprojetos', {
            url: '/gp/tiposprojetos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Gp\TiposprojetosListController',
            controllerAs: 'gp_tpsprjts_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}]