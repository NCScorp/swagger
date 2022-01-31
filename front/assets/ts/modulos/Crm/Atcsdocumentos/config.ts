export const AtcsdocumentosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmAtcsdocumentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.negociodocumento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.negociodocumento)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcsdocumentos_not_found', $stateParams)) {
                                        $state.go('crm_atcsdocumentos_not_found', $stateParams);
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
        'negocioFilter': ['CrmAtcs', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.negocio) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.negocio)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            } else {
                return {};
            }
        }]
    };
    $stateProvider
        .state('crm_atcsdocumentos', {
            url: '/crm/atcsdocumentos?q?negocio?status?tipodocumento.tipodocumento',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcsdocumentosListController',
            controllerAs: 'crm_tcsdcmnts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_atcsdocumentos_new', {
            url: '/crm/atcsdocumentos/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmAtcsdocumentosFormNewController',
            controllerAs: 'crm_tcsdcmnts_frm_nw_cntrllr'
        }).state('crm_atcsdocumentos_show', {
            url: '/crm/atcsdocumentos/:negociodocumento',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmAtcsdocumentosFormShowController',
            controllerAs: 'crm_tcsdcmnts_frm_shw_cntrllr'
        }).state('crm_atcsdocumentos_edit', {
            url: '/crm/atcsdocumentos/:negociodocumento/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\AtcsdocumentosFormController',
            controllerAs: 'crm_tcsdcmnts_frm_cntrllr'
        });
}];

