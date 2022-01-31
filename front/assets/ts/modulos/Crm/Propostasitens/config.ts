export const PropostasitensRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmPropostasitens', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.propostaitem) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negocio, $stateParams.proposta, $stateParams.propostaitem)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_propostasitens_not_found', $stateParams)) {
                                        $state.go('crm_propostasitens_not_found', $stateParams);
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
        .state('crm_propostasitens', {
            url: '/{negocio}/{proposta}/crm/propostasitens?q?propostacapitulo?fornecedor',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\PropostasitensListController',
            controllerAs: 'crm_prpststns_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_propostasitens_new', {
            url: '/{negocio}/{proposta}/crm/propostasitens/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmPropostasitensFormNewController',
            controllerAs: 'crm_prpststns_frm_nw_cntrllr'
        }).state('crm_propostasitens_show', {
            url: '/{negocio}/{proposta}/crm/propostasitens/:propostaitem',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmPropostasitensFormShowController',
            controllerAs: 'crm_prpststns_frm_shw_cntrllr'
        }).state('crm_propostasitens_edit', {
            url: '/{negocio}/{proposta}/crm/propostasitens/:propostaitem/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\PropostasitensFormController',
            controllerAs: 'crm_prpststns_frm_cntrllr'
        });
}]