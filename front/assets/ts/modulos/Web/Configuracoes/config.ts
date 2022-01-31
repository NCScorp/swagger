export const ConfiguracoesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['WebConfiguracoes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.configuracao) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.configuracao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('web_configuracoes_not_found', $stateParams)) {
                                        $state.go('web_configuracoes_not_found', $stateParams);
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
        .state('web_configuracoes', {
            url: '/web/configuracoes?q?chave?valor?sistema',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Web\ConfiguracoesListController',
            controllerAs: 'wb_cnfgrcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('web_configuracoes_show', {
            parent: 'web_configuracoes',
            url: '/:configuracao',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'WebConfiguracoesFormShowController',
            controllerAs: 'wb_cnfgrcs_frm_shw_cntrllr'
        });
}];

