export const HistoricoatcsRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmHistoricoatcs', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.historiconegocio) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negocio, $stateParams.historiconegocio)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_historicoatcs_not_found', $stateParams)) {
                                        $state.go('crm_historicoatcs_not_found', $stateParams);
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
        .state('crm_historicoatcs', {
            url: '/{negocio}/crm/historicoatcs?q?secao',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\HistoricoatcsListController',
            controllerAs: 'crm_hstrctcs_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

