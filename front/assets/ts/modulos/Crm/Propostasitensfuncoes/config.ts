 export const PropostasitensfuncoesRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmPropostasitensfuncoes', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.propostaitemfuncao) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.propostaitem, $stateParams.propostaitemfuncao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_propostasitensfuncoes_not_found', $stateParams)) {
                                        $state.go('crm_propostasitensfuncoes_not_found', $stateParams);
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
        .state('crm_propostasitensfuncoes', {
            url: '/{propostaitem}/crm/propostasitensfuncoes?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\PropostasitensfuncoesListController',
            controllerAs: 'crm_prpststnsfncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_propostasitensfuncoes_new', {
            url: '/{propostaitem}/crm/propostasitensfuncoes/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmPropostasitensfuncoesFormNewController',
            controllerAs: 'crm_prpststnsfncs_frm_nw_cntrllr'
        }).state('crm_propostasitensfuncoes_show', {
            url: '/{propostaitem}/crm/propostasitensfuncoes/:propostaitemfuncao',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmPropostasitensfuncoesFormShowController',
            controllerAs: 'crm_prpststnsfncs_frm_shw_cntrllr'
        }).state('crm_propostasitensfuncoes_edit', {
            url: '/{propostaitem}/crm/propostasitensfuncoes/:propostaitemfuncao/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\PropostasitensfuncoesFormController',
            controllerAs: 'crm_prpststnsfncs_frm_cntrllr'
        });
}]