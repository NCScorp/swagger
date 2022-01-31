export const FuncoesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['GpFuncoes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.funcao) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.funcao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('gp_funcoes_not_found', $stateParams)) {
                                        $state.go('gp_funcoes_not_found', $stateParams);
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
        .state('gp_funcoes', {
            url: '/gp/funcoes?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Gp\FuncoesListController',
            controllerAs: 'gp_fncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('gp_funcoes_new', {
            parent: 'gp_funcoes',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'GpFuncoesFormNewController',
            controllerAs: 'gp_fncs_frm_nw_cntrllr'
        }).state('gp_funcoes_show', {
            parent: 'gp_funcoes',
            url: '/:funcao',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'GpFuncoesFormShowController',
            controllerAs: 'gp_fncs_frm_shw_cntrllr'
        }).state('gp_funcoes_edit', {
            parent: 'gp_funcoes',
            url: '/:funcao/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Gp\FuncoesFormController',
            controllerAs: 'gp_fncs_frm_cntrllr'
        });
}];

