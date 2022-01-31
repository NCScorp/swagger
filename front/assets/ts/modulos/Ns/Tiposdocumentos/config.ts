export const TiposdocumentosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsTiposdocumentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tipodocumento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.tipodocumento)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                console.error(error);
                                if (error.status === 404) {
                                    if ($state.href('ns_tiposdocumentos_not_found', $stateParams)) {
                                        $state.go('ns_tiposdocumentos_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    entityService.novo = true;
                    return {};
                }
            }
        }]
    };
    let resolveFilter = {
    };
    $stateProvider
        .state('ns_tiposdocumentos', {
            url: '/ns/tiposdocumentos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\TiposdocumentosListController',
            controllerAs: 'ns_tpsdcmnts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_tiposdocumentos_new', {
            parent: 'ns_tiposdocumentos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsTiposdocumentosFormNewController',
            controllerAs: 'ns_tpsdcmnts_frm_nw_cntrllr'
        })
        .state('ns_tiposdocumentos_show', {
            parent: 'ns_tiposdocumentos',
            url: '/:tipodocumento',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsTiposdocumentosFormShowController',
            controllerAs: 'ns_tpsdcmnts_frm_shw_cntrllr'
        })
        .state('ns_tiposdocumentos_edit', {
            parent: 'ns_tiposdocumentos',
            url: '/:tipodocumento/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\TiposdocumentosFormController',
            controllerAs: 'ns_tpsdcmnts_frm_cntrllr'
        });
}]