export const ResponsabilidadesfinanceirasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmResponsabilidadesfinanceiras', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.responsabilidadefinanceira) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negocio, $stateParams.responsabilidadefinanceira)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_responsabilidadesfinanceiras_not_found', $stateParams)) {
                                        $state.go('crm_responsabilidadesfinanceiras_not_found', $stateParams);
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
        .state('crm_responsabilidadesfinanceiras', {
            url: '/{negocio}/crm/responsabilidadesfinanceiras?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\ResponsabilidadesfinanceirasListController',
            controllerAs: 'crm_rspnsblddsfnncrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_responsabilidadesfinanceiras_new', {
            url: '/{negocio}/crm/responsabilidadesfinanceiras/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmResponsabilidadesfinanceirasFormNewController',
            controllerAs: 'crm_rspnsblddsfnncrs_frm_nw_cntrllr'
        }).state('crm_responsabilidadesfinanceiras_show', {
            url: '/{negocio}/crm/responsabilidadesfinanceiras/:responsabilidadefinanceira',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmResponsabilidadesfinanceirasFormShowController',
            controllerAs: 'crm_rspnsblddsfnncrs_frm_shw_cntrllr'
        }).state('crm_responsabilidadesfinanceiras_edit', {
            url: '/{negocio}/crm/responsabilidadesfinanceiras/:responsabilidadefinanceira/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\ResponsabilidadesfinanceirasFormController',
            controllerAs: 'crm_rspnsblddsfnncrs_frm_cntrllr'
        });
}]