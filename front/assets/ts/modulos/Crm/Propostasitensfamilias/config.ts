export const PropostasitensfamiliasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmPropostasitensfamilias', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.propostaitemfamilia) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.propostaitem, $stateParams.propostaitemfamilia)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_propostasitensfamilias_not_found', $stateParams)) {
                                        $state.go('crm_propostasitensfamilias_not_found', $stateParams);
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
        .state('crm_propostasitensfamilias', {
            url: '/{propostaitem}/crm/propostasitensfamilias?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\PropostasitensfamiliasListController',
            controllerAs: 'crm_prpststnsfmls_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_propostasitensfamilias_new', {
            url: '/{propostaitem}/crm/propostasitensfamilias/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmPropostasitensfamiliasFormNewController',
            controllerAs: 'crm_prpststnsfmls_frm_nw_cntrllr'
        }).state('crm_propostasitensfamilias_show', {
            url: '/{propostaitem}/crm/propostasitensfamilias/:propostaitemfamilia',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmPropostasitensfamiliasFormShowController',
            controllerAs: 'crm_prpststnsfmls_frm_shw_cntrllr'
        }).state('crm_propostasitensfamilias_edit', {
            url: '/{propostaitem}/crm/propostasitensfamilias/:propostaitemfamilia/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\PropostasitensfamiliasFormController',
            controllerAs: 'crm_prpststnsfmls_frm_cntrllr'
        });
}]