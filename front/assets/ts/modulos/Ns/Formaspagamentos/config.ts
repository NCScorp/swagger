export const FormaspagamentosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsFormaspagamentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.formapagamento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.formapagamento)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_formaspagamentos_not_found', $stateParams)) {
                                        $state.go('ns_formaspagamentos_not_found', $stateParams);
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
        .state('ns_formaspagamentos', {
            url: '/ns/formaspagamentos?q?bloqueada',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\FormaspagamentosListController',
            controllerAs: 'ns_frmspgmnts_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}];

