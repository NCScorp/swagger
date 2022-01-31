import angular from 'angular';

export const AtcClienteFornecedoresContatosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

        let resolve = {
            'entity': ['CrmAtcclientefornecedorescontatos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                if ($stateParams.entity) {
                    return $stateParams.entity;
                } else {
                    if ($stateParams.contato) {
                        return new Promise((resolve: any, reject: any) => {
                            entityService.get($stateParams.negocio, $stateParams.contato)
                                .then((data: any) => {
                                    resolve(data);
                                })
                                .catch((error: any) => {
                                    if (error.status === 404) {
                                        if ($state.href('crm_atcclientefornecedorescontatos_not_found', $stateParams)) {
                                            $state.go('crm_atcclientefornecedorescontatos_not_found', $stateParams);
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
            .state('crm_atcclientefornecedorescontatos', {
                url: '/{negocio}/crm/atcclientefornecedorescontatos?q',
                resolve: resolveFilter,
                template: require('./index.html'),
                controller: 'Crm\AtcclientefornecedorescontatosListController',
                controllerAs: 'crm_tcclntfrncdrscntts_lst_cntrllr',
                reloadOnSearch: false
            })
            ;
    }];

