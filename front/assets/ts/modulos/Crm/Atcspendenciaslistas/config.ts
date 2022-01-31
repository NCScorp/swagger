export const AtcspendenciaslistasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcspendenciaslistas', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.negociopendencialista) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negociopendencialista)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcspendenciaslistas_not_found', $stateParams)) {
                                        $state.go('crm_atcspendenciaslistas_not_found', $stateParams);
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
        .state('crm_atcspendenciaslistas', {
            url: '/crm/atcspendenciaslistas?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcspendenciaslistasListController',
            controllerAs: 'crm_tcspndncslsts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_atcspendenciaslistas_new', {
            url: '/crm/atcspendenciaslistas/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmAtcspendenciaslistasFormNewController',
            controllerAs: 'crm_tcspndncslsts_frm_nw_cntrllr'
        }).state('crm_atcspendenciaslistas_show', {
            url: '/crm/atcspendenciaslistas/:negociopendencialista',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmAtcspendenciaslistasFormShowController',
            controllerAs: 'crm_tcspndncslsts_frm_shw_cntrllr'
        }).state('crm_atcspendenciaslistas_edit', {
            url: '/crm/atcspendenciaslistas/:negociopendencialista/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\AtcspendenciaslistasFormController',
            controllerAs: 'crm_tcspndncslsts_frm_cntrllr'
        });
}];

