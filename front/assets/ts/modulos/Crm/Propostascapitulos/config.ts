export const PropostascapitulosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmPropostascapitulos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.propostacapitulo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.proposta, $stateParams.propostacapitulo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_propostascapitulos_not_found', $stateParams)) {
                                        $state.go('crm_propostascapitulos_not_found', $stateParams);
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
        .state('crm_propostascapitulos', {
            url: '/{proposta}/crm/propostascapitulos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\PropostascapitulosListController',
            controllerAs: 'crm_prpstscptls_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_propostascapitulos_new', {
            url: '/{proposta}/crm/propostascapitulos/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmPropostascapitulosFormNewController',
            controllerAs: 'crm_prpstscptls_frm_nw_cntrllr'
        }).state('crm_propostascapitulos_edit', {
            url: '/{proposta}/crm/propostascapitulos/:propostacapitulo/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\PropostascapitulosFormController',
            controllerAs: 'crm_prpstscptls_frm_cntrllr'
        });
}]