export const EstabelecimentosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsEstabelecimentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.estabelecimento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.estabelecimento)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_estabelecimentos_not_found', $stateParams)) {
                                        $state.go('ns_estabelecimentos_not_found', $stateParams);
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
        .state('ns_estabelecimentos', {
            url: '/ns/estabelecimentos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\EstabelecimentosListController',
            controllerAs: 'ns_stblcmnts_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

