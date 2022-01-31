export const PessoasLogoRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsPessoasLogos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.pessoalogo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.idpessoa, $stateParams.pessoalogo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_pessoas_logos_not_found', $stateParams)) {
                                        $state.go('ns_pessoas_logos_not_found', $stateParams);
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
        .state('ns_pessoas_logos', {
            url: '/{idpessoa}/ns/pessoas/logos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\Pessoas\LogosListController',
            controllerAs: 'ns_psss_lgs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_pessoas_logos_new', {
            parent: 'ns_pessoas_logos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsPessoasLogosFormNewController',
            controllerAs: 'ns_psss_lgs_frm_nw_cntrllr'
        });
}]