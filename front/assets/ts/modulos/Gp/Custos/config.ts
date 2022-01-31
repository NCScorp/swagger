export const CustosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['GpCustos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.custo) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.custo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('gp_custos_not_found', $stateParams)) {
                                        $state.go('gp_custos_not_found', $stateParams);
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
        .state('gp_custos', {
            url: '/gp/custos?q',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Gp\CustosListController',
            controllerAs: 'gp_csts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('gp_custos_new', {
            parent: 'gp_custos',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'GpCustosFormNewController',
            controllerAs: 'gp_csts_frm_nw_cntrllr'
        }).state('gp_custos_edit', {
            parent: 'gp_custos',
            url: '/:custo/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Gp\CustosFormController',
            controllerAs: 'gp_csts_frm_cntrllr'
        });
}];

