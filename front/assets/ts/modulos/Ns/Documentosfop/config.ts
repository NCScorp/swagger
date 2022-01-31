export const DocumentosfopRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsDocumentosfop', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.documentofop) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.documentofop)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_documentosfop_not_found', $stateParams)) {
                                        $state.go('ns_documentosfop_not_found', $stateParams);
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
        .state('ns_documentosfop', {
            url: '/ns/documentosfop?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\DocumentosfopListController',
            controllerAs: 'ns_dcmntsfp_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

