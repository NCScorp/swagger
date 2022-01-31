export const acordosterceirizacoesservicosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

        let resolve = {
            'entity': ['CrmAcordosterceirizacoesservicos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                if ($stateParams.entity) {
                    return $stateParams.entity;
                } else {
                    if ($stateParams.acordoterceirizacaoservico) {
                        return new Promise((resolve: any, reject: any) => {
                            entityService.get($stateParams.acordoterceirizacaoservico)
                                .then((data: any) => {
                                    resolve(data);
                                })
                                .catch((error: any) => {
                                    if (error.status === 404) {
                                        if ($state.href('crm_acordosterceirizacoesservicos_not_found', $stateParams)) {
                                            $state.go('crm_acordosterceirizacoesservicos_not_found', $stateParams);
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
            .state('crm_acordosterceirizacoesservicos', {
                url: '/crm/acordosterceirizacoesservicos?q',
                resolve: resolveFilter,
                template: require('./index.html'),
                controller: 'Crm\AcordosterceirizacoesservicosListController',
                controllerAs: 'crm_crdstrcrzcssrvcs_lst_cntrllr',
                reloadOnSearch: false
            })
            .state('crm_acordosterceirizacoesservicos_new', {
                parent: 'crm_acordosterceirizacoesservicos',
                url: '/new',
                resolve: resolve,
                template: require('./new.html'),
                controller: 'CrmAcordosterceirizacoesservicosFormNewController',
                controllerAs: 'crm_crdstrcrzcssrvcs_frm_nw_cntrllr'
            }).state('crm_acordosterceirizacoesservicos_show', {
                parent: 'crm_acordosterceirizacoesservicos',
                url: '/:acordoterceirizacaoservico',
                resolve: resolve,
                template: require('./show.html'),
                controller: 'CrmAcordosterceirizacoesservicosFormShowController',
                controllerAs: 'crm_crdstrcrzcssrvcs_frm_shw_cntrllr'
            }).state('crm_acordosterceirizacoesservicos_edit', {
                parent: 'crm_acordosterceirizacoesservicos',
                url: '/:acordoterceirizacaoservico/edit',
                resolve: resolve,
                template: require('./form.html'),
                controller: 'Crm\AcordosterceirizacoesservicosFormController',
                controllerAs: 'crm_crdstrcrzcssrvcs_frm_cntrllr'
            });
}]

