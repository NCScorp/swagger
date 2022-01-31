export const LayoutsdocumentosimpressosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsLayoutsdocumentosimpressos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.layoutdocumentoimpresso) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.layoutdocumentoimpresso)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_layoutsdocumentosimpressos_not_found', $stateParams)) {
                                        $state.go('ns_layoutsdocumentosimpressos_not_found', $stateParams);
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
        .state('ns_layoutsdocumentosimpressos', {
            url: '/ns/layoutsdocumentosimpressos?q?sistema?nomedocumento',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\LayoutsdocumentosimpressosListController',
            controllerAs: 'ns_lytsdcmntsmprsss_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_layoutsdocumentosimpressos_show', {
            parent: 'ns_layoutsdocumentosimpressos',
            url: '/:layoutdocumentoimpresso',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsLayoutsdocumentosimpressosFormShowController',
            controllerAs: 'ns_lytsdcmntsmprsss_frm_shw_cntrllr'
        });
}];

