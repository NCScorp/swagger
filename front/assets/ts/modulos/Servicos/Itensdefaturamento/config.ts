 export const ItensdefaturamentoRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['ServicosItensdefaturamento', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.servico) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.servico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('servicos_itensdefaturamento_not_found', $stateParams)) {
                                        $state.go('servicos_itensdefaturamento_not_found', $stateParams);
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
        .state('servicos_itensdefaturamento', {
            url: '/servicos/itensdefaturamento?q?bloqueado',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Servicos\ItensdefaturamentoListController',
            controllerAs: 'srvcs_tnsdftrmnt_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

