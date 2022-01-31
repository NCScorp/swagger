export const OrcamentoRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmOrcamentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.orcamento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.orcamento)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_orcamentos_not_found', $stateParams)) {
                                        $state.go('crm_orcamentos_not_found', $stateParams);
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
        .state('crm_orcamentos', {
            url: '/crm/orcamentos?q?propostaitem?propostaitemfamilia?propostaitemfuncao?execucaodeservico?itemfaturamento?fornecedor?propostaitem.negocio',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\OrcamentosListController',
            controllerAs: 'crm_rcmnts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_orcamentos_new', {
            url: '/crm/orcamentos/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmOrcamentosFormNewController',
            controllerAs: 'crm_rcmnts_frm_nw_cntrllr'
        }).state('crm_orcamentos_show', {
            url: '/crm/orcamentos/:orcamento',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmOrcamentosFormShowController',
            controllerAs: 'crm_rcmnts_frm_shw_cntrllr'
        }).state('crm_orcamentos_edit', {
            url: '/crm/orcamentos/:orcamento/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\OrcamentosFormController',
            controllerAs: 'crm_rcmnts_frm_cntrllr'
        });
}]