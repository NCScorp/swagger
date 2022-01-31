
export const ResponsabilidadesfinanceirasvaloresRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmResponsabilidadesfinanceirasvalores', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.responsabilidadefinanceiravalor) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.responsabilidadefinanceira, $stateParams.responsabilidadefinanceiravalor)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_responsabilidadesfinanceirasvalores_not_found', $stateParams)) {
                                        $state.go('crm_responsabilidadesfinanceirasvalores_not_found', $stateParams);
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
        .state('crm_responsabilidadesfinanceirasvalores', {
            url: '/{responsabilidadefinanceira}/crm/responsabilidadesfinanceirasvalores?q?responsavelfinanceiro',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ResponsabilidadesfinanceirasvaloresListController',
            controllerAs: 'crm_rspnsblddsfnncrsvlrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_responsabilidadesfinanceirasvalores_new', {
            url: '/{responsabilidadefinanceira}/crm/responsabilidadesfinanceirasvalores/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmResponsabilidadesfinanceirasvaloresFormNewController',
            controllerAs: 'crm_rspnsblddsfnncrsvlrs_frm_nw_cntrllr'
        }).state('crm_responsabilidadesfinanceirasvalores_show', {
            url: '/{responsabilidadefinanceira}/crm/responsabilidadesfinanceirasvalores/:responsabilidadefinanceiravalor',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmResponsabilidadesfinanceirasvaloresFormShowController',
            controllerAs: 'crm_rspnsblddsfnncrsvlrs_frm_shw_cntrllr'
        }).state('crm_responsabilidadesfinanceirasvalores_edit', {
            url: '/{responsabilidadefinanceira}/crm/responsabilidadesfinanceirasvalores/:responsabilidadefinanceiravalor/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\ResponsabilidadesfinanceirasvaloresFormController',
            controllerAs: 'crm_rspnsblddsfnncrsvlrs_frm_cntrllr'
        });
}]