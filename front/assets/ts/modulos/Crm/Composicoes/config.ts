export const composicoesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmComposicoes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.composicao) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.composicao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_composicoes_not_found', $stateParams)) {
                                        $state.go('crm_composicoes_not_found', $stateParams);
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
        .state('crm_composicoes', {
            url: '/crm/composicoes?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ComposicoesListController',
            controllerAs: 'crm_cmpscs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_composicoes_new', {
            parent: 'crm_composicoes',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmComposicoesFormNewController',
            controllerAs: 'crm_cmpscs_frm_nw_cntrllr'
        }).state('crm_composicoes_show', {
            parent: 'crm_composicoes',
            url: '/:composicao',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmComposicoesFormShowController',
            controllerAs: 'crm_cmpscs_frm_shw_cntrllr'
        }).state('crm_composicoes_edit', {
            parent: 'crm_composicoes',
            url: '/:composicao/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\ComposicoesFormController',
            controllerAs: 'crm_cmpscs_frm_cntrllr'
        });
}];

