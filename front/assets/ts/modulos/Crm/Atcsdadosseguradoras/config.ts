export const AtcsdadosseguradorasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcsdadosseguradoras', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.negociodadosseguradora) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negocio, $stateParams.negociodadosseguradora)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcsdadosseguradoras_not_found', $stateParams)) {
                                        $state.go('crm_atcsdadosseguradoras_not_found', $stateParams);
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
        .state('crm_atcsdadosseguradoras', {
            url: '/{negocio}/crm/atcsdadosseguradoras?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcsdadosseguradorasListController',
            controllerAs: 'crm_tcsddssgrdrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_atcsdadosseguradoras_new', {
            parent: 'crm_atcsdadosseguradoras',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmAtcsdadosseguradorasFormNewController',
            controllerAs: 'crm_tcsddssgrdrs_frm_nw_cntrllr'
        }).state('crm_atcsdadosseguradoras_edit', {
            parent: 'crm_atcsdadosseguradoras',
            url: '/:negociodadosseguradora/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\AtcsdadosseguradorasFormController',
            controllerAs: 'crm_tcsddssgrdrs_frm_cntrllr'
        });
}];

